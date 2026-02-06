<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class EmployeeOvertimeExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $overtimes;
    protected $worker;

    public function __construct(Collection $overtimes, $worker)
    {
        $this->overtimes = $overtimes;
        $this->worker = $worker;
    }

    public function collection()
    {
        return $this->overtimes;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Pengajuan',
            'Tanggal Lembur',
            'Waktu Mulai',
            'Waktu Selesai',
            'Total Jam',
            'Status',
            'Alasan',
        ];
    }

    public function map($overtime): array
    {
        static $no = 0;
        $no++;

        $status = match($overtime->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($overtime->status ?? '-')
        };

        return [
            $no,
            $overtime->created_at?->format('d/m/Y') ?? '-',
            $overtime->overtime_date?->format('d/m/Y') ?? '-',
            $overtime->start_time ?? '-',
            $overtime->end_time ?? '-',
            $overtime->total_hours ? $overtime->total_hours . ' jam' : '-',
            $status,
            $overtime->reason ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set header style
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '16A34A'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Set all cells border
        $lastRow = $this->overtimes->count() + 1;
        $sheet->getStyle('A1:H' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Zebra striping
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ],
                ]);
            }
        }

        return [];
    }
}
