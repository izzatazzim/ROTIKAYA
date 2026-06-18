<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(array $filters, User $user): array
    {
        $invoiceQuery = $this->baseInvoiceQuery($filters, $user);
        $paymentQuery = $this->basePaymentQuery($filters, $user);

        // Revenue KPI follows sales/invoice production volume in scope,
        // which remains visible even before payment collection happens.
        $revenue = (float) (clone $invoiceQuery)->sum('invoices.total_amount');
        $totalInvoices = (int) (clone $invoiceQuery)->count();
        $overdueInvoices = (int) (clone $invoiceQuery)->where('invoices.status', 'overdue')->count();
        $overduePercentage = $totalInvoices > 0 ? (int) round(($overdueInvoices / $totalInvoices) * 100) : 0;

        $activeClients = (int) (clone $invoiceQuery)
            ->distinct('sales.client_id')
            ->count('sales.client_id');

        $newPayments = (int) (clone $paymentQuery)->count();

        return [
            'revenue' => $revenue,
            'overdue_percentage' => $overduePercentage,
            'active_clients' => $activeClients,
            'new_payments' => $newPayments,
            'overdue_invoices' => $overdueInvoices,
        ];
    }

    public function getChartData(array $filters, User $user): array
    {
        $start = Carbon::parse($filters['start']);
        $end = Carbon::parse($filters['end']);
        $days = $start->diffInDays($end) + 1;

        if ($days <= 90) {
            return $this->dailyChartData($filters, $user, $start, $end);
        }

        if ($days <= 365) {
            return $this->monthlyChartData($filters, $user, $start, $end);
        }

        return $this->quarterlyChartData($filters, $user, $start, $end);
    }

    public function getRecentPayments(array $filters, User $user): Collection
    {
        return $this->basePaymentQuery($filters, $user)
            ->with(['invoice.sale.client'])
            ->orderByDesc('payments.payment_date')
            ->limit(5)
            ->get();
    }

    private function baseInvoiceQuery(array $filters, User $user): Builder
    {
        $query = Invoice::query()
            ->join('sales', 'sales.id', '=', 'invoices.sale_id')
            ->whereDate('sales.created_at', '>=', $filters['start'])
            ->whereDate('sales.created_at', '<=', $filters['end']);

        if (! empty($filters['client_id'])) {
            $query->where('sales.client_id', (int) $filters['client_id']);
        }

        if ($user->hasRole('sales_staff')) {
            $query->where('sales.salesperson_id', $user->id);
        } elseif (! empty($filters['salesperson_id'])) {
            $query->where('sales.salesperson_id', (int) $filters['salesperson_id']);
        }

        return $query;
    }

    private function basePaymentQuery(array $filters, User $user): Builder
    {
        $query = Payment::query()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->join('sales', 'sales.id', '=', 'invoices.sale_id')
            ->whereDate('payments.payment_date', '>=', $filters['start'])
            ->whereDate('payments.payment_date', '<=', $filters['end']);

        if (! empty($filters['client_id'])) {
            $query->where('sales.client_id', (int) $filters['client_id']);
        }

        if ($user->hasRole('sales_staff')) {
            $query->where('sales.salesperson_id', $user->id);
        } elseif (! empty($filters['salesperson_id'])) {
            $query->where('sales.salesperson_id', (int) $filters['salesperson_id']);
        }

        return $query->select('payments.*');
    }

    private function dailyChartData(array $filters, User $user, Carbon $start, Carbon $end): array
    {
        $rows = $this->baseInvoiceQuery($filters, $user)
            ->selectRaw('invoices.issue_date as bucket, COUNT(*) as total')
            ->groupBy('invoices.issue_date')
            ->pluck('total', 'bucket');

        $labels = [];
        $values = [];

        foreach (CarbonPeriod::create($start, $end) as $day) {
            $key = $day->toDateString();
            $labels[] = $day->format('M d');
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values, 'granularity' => 'daily'];
    }

    private function monthlyChartData(array $filters, User $user, Carbon $start, Carbon $end): array
    {
        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'sqlite' ? "CAST(strftime('%Y', invoices.issue_date) as integer)" : 'YEAR(invoices.issue_date)';
        $monthExpr = $driver === 'sqlite' ? "CAST(strftime('%m', invoices.issue_date) as integer)" : 'MONTH(invoices.issue_date)';

        $rows = $this->baseInvoiceQuery($filters, $user)
            ->selectRaw("{$yearExpr} as year_num, {$monthExpr} as month_num, COUNT(*) as total")
            ->groupBy('year_num', 'month_num')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->year_num, $row->month_num));

        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfMonth();
        $endCursor = $end->copy()->startOfMonth();

        while ($cursor->lte($endCursor)) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $values[] = (int) ($rows[$key]->total ?? 0);
            $cursor->addMonth();
        }

        return ['labels' => $labels, 'values' => $values, 'granularity' => 'monthly'];
    }

    private function quarterlyChartData(array $filters, User $user, Carbon $start, Carbon $end): array
    {
        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'sqlite' ? "CAST(strftime('%Y', invoices.issue_date) as integer)" : 'YEAR(invoices.issue_date)';
        $quarterExpr = $driver === 'sqlite'
            ? "CAST(((CAST(strftime('%m', invoices.issue_date) as integer) - 1) / 3) + 1 as integer)"
            : 'QUARTER(invoices.issue_date)';

        $rows = $this->baseInvoiceQuery($filters, $user)
            ->selectRaw("{$yearExpr} as year_num, {$quarterExpr} as quarter_num, COUNT(*) as total")
            ->groupBy('year_num', 'quarter_num')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-Q%d', $row->year_num, $row->quarter_num));

        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfQuarter();
        $endCursor = $end->copy()->startOfQuarter();

        while ($cursor->lte($endCursor)) {
            $key = sprintf('%04d-Q%d', $cursor->year, $cursor->quarter);
            $labels[] = sprintf('Q%d %d', $cursor->quarter, $cursor->year);
            $values[] = (int) ($rows[$key]->total ?? 0);
            $cursor->addMonths(3);
        }

        return ['labels' => $labels, 'values' => $values, 'granularity' => 'quarterly'];
    }
}
