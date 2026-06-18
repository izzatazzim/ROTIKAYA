<?php

namespace App\Exports\Sheets;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters)
    {
    }

    public function query(): Builder
    {
        $query = Payment::query()->with(['invoice.sale.client', 'invoice.sale.salesperson'])
            ->whereDate('payment_date', '>=', $this->filters['from_date'])
            ->whereDate('payment_date', '<=', $this->filters['to_date']);

        if (! empty($this->filters['client_id'])) {
            $clientId = (int) $this->filters['client_id'];
            $query->whereHas('invoice.sale', fn ($saleQuery) => $saleQuery->where('client_id', $clientId));
        }

        if (! empty($this->filters['campaign_name'])) {
            $campaign = (string) $this->filters['campaign_name'];
            $query->whereHas('invoice.sale', fn ($saleQuery) => $saleQuery->where('campaign_name', 'like', "%{$campaign}%"));
        }

        return $query->orderBy('payment_date');
    }

    public function map($payment): array
    {
        return [
            optional($payment->payment_date)->format('Y-m-d'),
            $payment->invoice?->invoice_number ?? '-',
            $payment->invoice?->sale?->client?->name ?? '-',
            (float) $payment->amount,
            $payment->method ?? '-',
            $payment->reference ?? '-',
            $payment->invoice?->sale?->salesperson?->name ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Payment Date',
            'Invoice No.',
            'Client Name',
            'Amount',
            'Payment Method',
            'Reference',
            'Recorded By',
        ];
    }

    public function title(): string
    {
        return 'Payments';
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
