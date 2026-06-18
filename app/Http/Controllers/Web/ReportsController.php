<?php

namespace App\Http\Controllers\Web;

use App\Exports\RevenueReportExport;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ReportLog;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::query()->with(['client', 'salesperson']);

        if ($request->filled('client_id')) {
            $query->where('client_id', (int) $request->input('client_id'));
        }
        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', (int) $request->input('salesperson_id'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $sales = $query->latest()->get();
        $total = $sales->sum('amount');

        $clients = Client::query()->orderBy('name')->get();
        $salespeople = User::query()->whereHas('role', fn($q) => $q->where('name', 'sales_staff'))->get();

        return view('reports.index', compact('sales', 'total', 'clients', 'salespeople'));
    }

    public function downloadPdf(Request $request, string $type)
    {
        $data = $this->getReportData($type, $request);

        $pdf = app('dompdf.wrapper')->loadView('reports.pdf.' . $type, $data);

        ReportLog::query()->create([
            'generated_by' => $request->user()->id,
            'report_type' => $type,
            'filters' => $request->only(['from_date', 'to_date']),
            'file_path' => null,
        ]);

        return $pdf->download($type . '-report-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function clientStatementForm()
    {
        return view('reports.client-statement', [
            'clients' => Client::query()->orderBy('name')->get(),
        ]);
    }

    public function exportClientStatement(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $client = Client::query()->findOrFail((int) $validated['client_id']);

        $invoices = Invoice::query()
            ->with('sale.client')
            ->whereHas('sale', fn ($query) => $query->where('client_id', $client->id))
            ->whereBetween('issue_date', [$validated['start_date'], $validated['end_date']])
            ->orderBy('issue_date')
            ->get();

        if ($invoices->isEmpty()) {
            return back()
                ->with('error', "No transactions found for {$client->name} between {$validated['start_date']} and {$validated['end_date']}. Try a broader date range or different client.")
                ->withInput();
        }

        $summary = [
            'total_invoiced' => (float) $invoices->sum('total_amount'),
            'total_paid' => (float) $invoices->sum('paid_amount'),
            'total_outstanding' => (float) $invoices->sum(fn (Invoice $invoice) => (float) $invoice->total_amount - (float) $invoice->paid_amount),
            'total_overdue' => (float) $invoices
                ->where('status', 'overdue')
                ->sum(fn (Invoice $invoice) => (float) $invoice->total_amount - (float) $invoice->paid_amount),
        ];

        $user = $request->user();
        $filename = sprintf(
            'client-statement-%s-%s-to-%s.pdf',
            Str::slug($client->name),
            $validated['start_date'],
            $validated['end_date']
        );

        $pdf = app('dompdf.wrapper')->loadView('reports.pdf.client-statement', [
            'client' => $client,
            'invoices' => $invoices,
            'summary' => $summary,
            'startDate' => $validated['start_date'],
            'endDate' => $validated['end_date'],
            'generatedAt' => now(),
            'generatedBy' => $user?->name ?? 'System',
        ]);

        ReportLog::query()->create([
            'generated_by' => $user->id,
            'report_type' => 'client_statement',
            'filters' => [
                'client_id' => (int) $validated['client_id'],
                'start' => $validated['start_date'],
                'end' => $validated['end_date'],
            ],
            'file_path' => null,
        ]);

        return $pdf->download($filename);
    }

    public function exportRevenuePdf(Request $request)
    {
        $filters = $this->validateRevenueFilters($request);
        $datasets = $this->buildRevenueDatasets($filters);

        if ($datasets['sales']->isEmpty() && $datasets['invoices']->isEmpty() && $datasets['payments']->isEmpty()) {
            return back()
                ->with('error', 'No data found for the selected filters. Try a broader date range.')
                ->withInput();
        }

        $summary = $this->buildRevenueSummary($datasets);

        $pdf = app('dompdf.wrapper')->loadView('reports.pdf.financial-summary', [
            'title' => 'Revenue Report',
            'date' => now()->format('F d, Y'),
            'period' => "{$filters['from_date']} to {$filters['to_date']}",
            'total_sales' => $summary['total_revenue'],
            'total_invoices' => $summary['total_invoices_count'],
            'total_payments' => $summary['total_paid_amount'],
            'overdue_invoices' => $summary['total_overdue_count'],
        ]);

        ReportLog::query()->create([
            'generated_by' => $request->user()->id,
            'report_type' => 'revenue_report_pdf',
            'filters' => $filters,
            'file_path' => null,
        ]);

        return $pdf->download($this->buildRevenueFilename($filters, 'pdf'));
    }

    public function exportRevenueXlsx(Request $request)
    {
        $filters = $this->validateRevenueFilters($request);
        $datasets = $this->buildRevenueDatasets($filters);

        if ($datasets['sales']->isEmpty() && $datasets['invoices']->isEmpty() && $datasets['payments']->isEmpty()) {
            return back()
                ->with('error', 'No data found for the selected filters. Try a broader date range.')
                ->withInput();
        }

        ReportLog::query()->create([
            'generated_by' => $request->user()->id,
            'report_type' => 'revenue_report_xlsx',
            'filters' => $filters,
            'file_path' => null,
        ]);

        return Excel::download(new RevenueReportExport($filters), $this->buildRevenueFilename($filters, 'xlsx'));
    }

    private function getReportData(string $type, Request $request): array
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', Carbon::now()->toDateString());

        return match ($type) {
            'balance-sheet' => $this->getBalanceSheetData($fromDate, $toDate),
            'profit-loss' => $this->getProfitLossData($fromDate, $toDate),
            'cash-flow' => $this->getCashFlowData($fromDate, $toDate),
            'financial-summary' => $this->getFinancialSummaryData($fromDate, $toDate),
            'receipt-summary' => $this->getReceiptSummaryData($fromDate, $toDate),
            'payment-voucher' => $this->getPaymentVoucherData($fromDate, $toDate),
            default => [],
        };
    }

    private function getBalanceSheetData(string $fromDate, string $toDate): array
    {
        $totalReceivables = Invoice::query()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('total_amount');

        $totalPaid = Payment::query()
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->sum('amount');

        return [
            'title' => 'Balance Sheet',
            'date' => Carbon::now()->format('F d, Y'),
            'period' => $fromDate . ' to ' . $toDate,
            'total_receivables' => $totalReceivables,
            'total_paid' => $totalPaid,
            'net_position' => $totalPaid - $totalReceivables,
        ];
    }

    private function getProfitLossData(string $fromDate, string $toDate): array
    {
        $totalRevenue = Sale::query()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('amount');

        $totalReceived = Payment::query()
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->sum('amount');

        return [
            'title' => 'Profit & Loss Statement',
            'date' => Carbon::now()->format('F d, Y'),
            'period' => $fromDate . ' to ' . $toDate,
            'total_revenue' => $totalRevenue,
            'total_received' => $totalReceived,
        ];
    }

    private function getCashFlowData(string $fromDate, string $toDate): array
    {
        $payments = Payment::query()
            ->with('invoice.sale.client')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->orderBy('payment_date', 'desc')
            ->get();

        return [
            'title' => 'Cash Flow Statement',
            'date' => Carbon::now()->format('F d, Y'),
            'period' => $fromDate . ' to ' . $toDate,
            'payments' => $payments,
            'total' => $payments->sum('amount'),
        ];
    }

    private function getFinancialSummaryData(string $fromDate, string $toDate): array
    {
        return [
            'title' => 'Financial Summary',
            'date' => Carbon::now()->format('F d, Y'),
            'period' => $fromDate . ' to ' . $toDate,
            'total_sales' => Sale::query()->whereBetween('created_at', [$fromDate, $toDate])->sum('amount'),
            'total_invoices' => Invoice::query()->whereBetween('issue_date', [$fromDate, $toDate])->count(),
            'total_payments' => Payment::query()->whereBetween('payment_date', [$fromDate, $toDate])->sum('amount'),
            'overdue_invoices' => Invoice::query()->where('status', 'overdue')->count(),
        ];
    }

    private function getReceiptSummaryData(string $fromDate, string $toDate): array
    {
        $payments = Payment::query()
            ->with('invoice.sale.client')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->orderBy('payment_date', 'desc')
            ->get();

        return [
            'title' => 'Receipt Summary',
            'date' => Carbon::now()->format('F d, Y'),
            'period' => $fromDate . ' to ' . $toDate,
            'payments' => $payments,
            'total' => $payments->sum('amount'),
        ];
    }

    private function getPaymentVoucherData(string $fromDate, string $toDate): array
    {
        $invoices = Invoice::query()
            ->with('sale.client', 'payments')
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->get();

        return [
            'title' => 'Payment Voucher Summary',
            'date' => Carbon::now()->format('F d, Y'),
            'period' => $fromDate . ' to ' . $toDate,
            'invoices' => $invoices,
        ];
    }

    private function validateRevenueFilters(Request $request): array
    {
        return $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'campaign_name' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function buildRevenueDatasets(array $filters): array
    {
        $salesQuery = Sale::query()->with(['client', 'salesperson'])
            ->whereDate('created_at', '>=', $filters['from_date'])
            ->whereDate('created_at', '<=', $filters['to_date']);

        $invoiceQuery = Invoice::query()->with(['sale.client', 'sale.salesperson'])
            ->whereDate('issue_date', '>=', $filters['from_date'])
            ->whereDate('issue_date', '<=', $filters['to_date']);

        $paymentQuery = Payment::query()->with(['invoice.sale.client', 'invoice.sale.salesperson'])
            ->whereDate('payment_date', '>=', $filters['from_date'])
            ->whereDate('payment_date', '<=', $filters['to_date']);

        if (! empty($filters['client_id'])) {
            $clientId = (int) $filters['client_id'];
            $salesQuery->where('client_id', $clientId);
            $invoiceQuery->whereHas('sale', fn ($query) => $query->where('client_id', $clientId));
            $paymentQuery->whereHas('invoice.sale', fn ($query) => $query->where('client_id', $clientId));
        }

        if (! empty($filters['campaign_name'])) {
            $campaign = $filters['campaign_name'];
            $salesQuery->where('campaign_name', 'like', "%{$campaign}%");
            $invoiceQuery->whereHas('sale', fn ($query) => $query->where('campaign_name', 'like', "%{$campaign}%"));
            $paymentQuery->whereHas('invoice.sale', fn ($query) => $query->where('campaign_name', 'like', "%{$campaign}%"));
        }

        return [
            'sales' => $salesQuery->get(),
            'invoices' => $invoiceQuery->get(),
            'payments' => $paymentQuery->get(),
        ];
    }

    private function buildRevenueSummary(array $datasets): array
    {
        return [
            'total_revenue' => (float) $datasets['sales']->sum('amount'),
            'total_invoices_count' => $datasets['invoices']->count(),
            'total_paid_amount' => (float) $datasets['payments']->sum('amount'),
            'total_outstanding_amount' => (float) $datasets['invoices']->sum(
                fn (Invoice $invoice) => (float) $invoice->total_amount - (float) $invoice->paid_amount
            ),
            'total_overdue_amount' => (float) $datasets['invoices']
                ->where('status', 'overdue')
                ->sum(fn (Invoice $invoice) => (float) $invoice->total_amount - (float) $invoice->paid_amount),
            'total_overdue_count' => $datasets['invoices']->where('status', 'overdue')->count(),
        ];
    }

    private function buildRevenueFilename(array $filters, string $extension): string
    {
        $clientSegment = '';

        if (! empty($filters['client_id'])) {
            $clientName = Client::query()->find((int) $filters['client_id'])?->name;
            if ($clientName) {
                $clientSegment = Str::slug($clientName) . '-';
            }
        }

        return sprintf(
            'revenue-report-%s%s-to-%s.%s',
            $clientSegment,
            $filters['from_date'],
            $filters['to_date'],
            $extension
        );
    }
}
