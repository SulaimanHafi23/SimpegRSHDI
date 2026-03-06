<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class EmployeeBusinessTripExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $trips;
    protected $worker;

    public function __construct(Collection $trips, $worker)
    {
        $this->trips = $trips;
        $this->worker = $worker;
    }

    public function collection()
    {
        return $this->trips;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tujuan',
            'Keperluan',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi (Hari)',
            'Estimasi Biaya',
            'Status',
        ];
    }

    public function map($trip): array
    {
        static $no = 0;
        $no++;

        $duration = $trip->start_date && $trip->end_date
            ? $trip->start_date->diffInDays($trip->end_date) + 1
            : '-';

        return [
            $no,
            $trip->destination ?? '-',
            $trip->purpose ?? '-',
            $trip->start_date?->format('d/m/Y') ?? '-',
            $trip->end_date?->format('d/m/Y') ?? '-',
            $duration,
            $trip->estimated_cost ? 'Rp ' . number_format($trip->estimated_cost, 0, ',', '.') : '-',
            $this->getStatusLabel($trip->status),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '8B5CF6'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $lastRow = $this->trips->count() + 1;
        $sheet->getStyle('A1:H' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

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

    protected function getStatusLabel($status)
    {
        return match ($status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($status ?? '-'),
        };
    }
}
