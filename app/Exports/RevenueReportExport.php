<?php

namespace App\Exports;

use App\Exports\Sheets\InvoicesSheet;
use App\Exports\Sheets\PaymentsSheet;
use App\Exports\Sheets\SalesSheet;
use App\Exports\Sheets\SummarySheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RevenueReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $filters)
    {
    }

    public function sheets(): array
    {
        return [
            new SummarySheet($this->filters),
            new InvoicesSheet($this->filters),
            new PaymentsSheet($this->filters),
            new SalesSheet($this->filters),
        ];
    }
}
