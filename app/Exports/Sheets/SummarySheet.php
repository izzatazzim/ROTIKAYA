<?php

namespace App\Exports\Sheets;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummarySheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters)
    {
    }

    public function collection(): Collection
    {
        $salesQuery = Sale::query()->with(['client', 'salesperson']);
        $invoicesQuery = Invoice::query()->with(['sale.client', 'sale.salesperson']);
        $paymentsQuery = Payment::query()->with(['invoice.sale.client', 'invoice.sale.salesperson']);

        $this->applyFilters($salesQuery, $invoicesQuery, $paymentsQuery);

        $sales = $salesQuery->get();
        $invoices = $invoicesQuery->get();
        $payments = $paymentsQuery->get();

        $topCampaigns = $sales
            ->groupBy('campaign_name')
            ->map(fn (Collection $group) => (float) $group->sum('amount'))
            ->sortDesc()
            ->take(5);

        $rows = collect([
            [
                'period_start' => $this->filters['from_date'],
                'period_end' => $this->filters['to_date'],
                'total_revenue' => (float) $sales->sum('amount'),
                'total_invoices' => $invoices->count(),
                'total_paid_amount' => (float) $payments->sum('amount'),
                'total_outstanding_amount' => (float) $invoices->sum(fn (Invoice $invoice) => (float) $invoice->total_amount - (float) $invoice->paid_amount),
                'total_overdue_amount' => (float) $invoices
                    ->where('status', 'overdue')
                    ->sum(fn (Invoice $invoice) => (float) $invoice->total_amount - (float) $invoice->paid_amount),
                'top_campaign_1' => $this->campaignCell($topCampaigns, 0),
                'top_campaign_2' => $this->campaignCell($topCampaigns, 1),
                'top_campaign_3' => $this->campaignCell($topCampaigns, 2),
                'top_campaign_4' => $this->campaignCell($topCampaigns, 3),
                'top_campaign_5' => $this->campaignCell($topCampaigns, 4),
            ],
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Period Start',
            'Period End',
            'Total Revenue',
            'Total Invoices Count',
            'Total Paid Amount',
            'Total Outstanding Amount',
            'Total Overdue Amount',
            'Top Campaign 1',
            'Top Campaign 2',
            'Top Campaign 3',
            'Top Campaign 4',
            'Top Campaign 5',
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ];
    }

    private function applyFilters($salesQuery, $invoicesQuery, $paymentsQuery): void
    {
        $from = $this->filters['from_date'];
        $to = $this->filters['to_date'];
        $clientId = $this->filters['client_id'] ?? null;
        $campaignName = $this->filters['campaign_name'] ?? null;

        $salesQuery->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $invoicesQuery->whereDate('issue_date', '>=', $from)
            ->whereDate('issue_date', '<=', $to);

        $paymentsQuery->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to);

        if ($clientId) {
            $salesQuery->where('client_id', (int) $clientId);
            $invoicesQuery->whereHas('sale', fn ($query) => $query->where('client_id', (int) $clientId));
            $paymentsQuery->whereHas('invoice.sale', fn ($query) => $query->where('client_id', (int) $clientId));
        }

        if ($campaignName) {
            $salesQuery->where('campaign_name', 'like', "%{$campaignName}%");
            $invoicesQuery->whereHas('sale', fn ($query) => $query->where('campaign_name', 'like', "%{$campaignName}%"));
            $paymentsQuery->whereHas('invoice.sale', fn ($query) => $query->where('campaign_name', 'like', "%{$campaignName}%"));
        }
    }

    private function campaignCell(Collection $topCampaigns, int $index): string
    {
        $pair = $topCampaigns->slice($index, 1)->all();
        if ($pair === []) {
            return '-';
        }

        $name = array_key_first($pair);
        $amount = (float) ($pair[$name] ?? 0);
        return sprintf('%s (RM%s)', $name, number_format($amount, 2));
    }
}
