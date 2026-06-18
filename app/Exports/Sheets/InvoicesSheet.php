<?php

namespace App\Exports\Sheets;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters)
    {
    }

    public function query(): Builder
    {
        $query = Invoice::query()->with(['sale.client', 'sale.salesperson'])
            ->whereDate('issue_date', '>=', $this->filters['from_date'])
            ->whereDate('issue_date', '<=', $this->filters['to_date']);

        if (! empty($this->filters['client_id'])) {
            $clientId = (int) $this->filters['client_id'];
            $query->whereHas('sale', fn ($saleQuery) => $saleQuery->where('client_id', $clientId));
        }

        if (! empty($this->filters['campaign_name'])) {
            $campaign = (string) $this->filters['campaign_name'];
            $query->whereHas('sale', fn ($saleQuery) => $saleQuery->where('campaign_name', 'like', "%{$campaign}%"));
        }

        return $query->orderBy('issue_date');
    }

    public function map($invoice): array
    {
        $balance = (float) $invoice->total_amount - (float) $invoice->paid_amount;

        return [
            $invoice->invoice_number,
            $invoice->sale?->client?->name ?? '-',
            optional($invoice->issue_date)->format('Y-m-d'),
            optional($invoice->due_date)->format('Y-m-d'),
            (float) $invoice->total_amount,
            (float) $invoice->paid_amount,
            $balance,
            $invoice->status,
            $invoice->sale?->salesperson?->name ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Invoice No.',
            'Client Name',
            'Issue Date',
            'Due Date',
            'Amount',
            'Amount Paid',
            'Balance',
            'Status',
            'Salesperson',
        ];
    }

    public function title(): string
    {
        return 'Invoices';
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
}
