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
        $period = $this->startDate->translatedFormat('d M Y') . ' - ' . $this->endDate->translatedFormat('d M Y');
        return [
            $this->padRow(['PEGAWAI', $this->worker->name ?? '-']),
            $this->padRow(['NIP', $this->worker->nip ?? '-']),
            $this->padRow(['DEPARTEMEN', $this->worker->department->name ?? '-']),
            $this->padRow(['PERIODE', $period]),
            $this->padRow(['']),
            [
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
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Info header styling (rows 1-4)
        foreach ([1, 2, 3, 4] as $row) {
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ECFDF5'],
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '6EE7B7'],
                    ],
                ],
            ]);
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('047857');
        }

        // Table header styling (row 6)
        $sheet->getStyle('A6:J6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => ['rgb' => '047857'],
                'endColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '047857'],
                ],
            ],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(25);

        // Data rows styling
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 6) {
            $sheet->getStyle('A7:J' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);

            // Alternating row colors
            for ($row = 7; $row <= $lastRow; $row++) {
                if (($row - 6) % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F9FAFB');
                }
            }
        }

        return [];
    }

    private function padRow(array $row): array
    {
        return array_pad($row, 10, '');
    }
}
