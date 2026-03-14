<?php

namespace App\Exports;

use App\Exports\LeaveRecapSummarySheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LeaveRecapExport implements WithMultipleSheets
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function sheets(): array
    {
        return [
            new LeaveExport($this->filters),
            new LeaveRecapSummarySheet($this->filters),
        ];
    }
}
