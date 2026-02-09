<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkerAttendanceCalendarExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $worker;
    protected $rows;
    protected $startDate;
    protected $endDate;

    public function __construct($worker, array $rows, $startDate, $endDate)
    {
        $this->worker = $worker;
        $this->rows = $rows;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection(): Collection
    {
        return collect($this->rows)->map(function ($row, $index) {
            return [
                $index + 1,
                $row['date'],
                $row['day_name'],
                $row['shift_name'],
                $row['shift_time'],
                $row['check_in'],
                $row['check_out'],
                $row['status'],
                $row['late'],
                $row['notes'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Hari',
            'Shift',
            'Jadwal Shift',
            'Check In',
            'Check Out',
            'Status',
            'Terlambat',
            'Catatan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }
}
