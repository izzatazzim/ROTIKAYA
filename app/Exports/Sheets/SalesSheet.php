<?php

namespace App\Exports\Sheets;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters)
    {
    }

    public function query(): Builder
    {
        $query = Sale::query()->with(['client', 'salesperson'])
            ->whereDate('created_at', '>=', $this->filters['from_date'])
            ->whereDate('created_at', '<=', $this->filters['to_date']);

        if (! empty($this->filters['client_id'])) {
            $query->where('client_id', (int) $this->filters['client_id']);
        }

        if (! empty($this->filters['campaign_name'])) {
            $campaign = (string) $this->filters['campaign_name'];
            $query->where('campaign_name', 'like', "%{$campaign}%");
        }

        return $query->orderBy('created_at');
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            optional($sale->created_at)->format('Y-m-d'),
            $sale->client?->name ?? '-',
            $sale->campaign_name,
            (float) $sale->amount,
            $sale->salesperson?->name ?? '-',
            $sale->status,
        ];
    }

    public function headings(): array
    {
        return [
            'Sale No.',
            'Sale Date',
            'Client',
            'Campaign',
            'Amount',
            'Salesperson',
            'Status',
        ];
    }

    public function title(): string
    {
        return 'Sales';
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
