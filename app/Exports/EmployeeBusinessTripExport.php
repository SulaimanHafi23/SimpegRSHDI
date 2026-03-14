<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeBusinessTripExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected Collection $trips;
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
            'Durasi',
            'Estimasi Biaya',
            'Status',
        ];
    }

    public function map($trip): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $trip->destination ?? '-',
            $trip->purpose ?? '-',
            $trip->start_date?->format('d/m/Y') ?? '-',
            $trip->end_date?->format('d/m/Y') ?? '-',
            $trip->duration_label,
            (float) ($trip->estimated_cost ?? 0),
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

    public function columnFormats(): array
    {
        return [
            'G' => '"Rp" #,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $totalRow = $this->trips->count() + 3;
                $sheet = $event->sheet;

                $sheet->setCellValue('F' . $totalRow, 'Total Estimasi Biaya');
                $sheet->setCellValue('G' . $totalRow, (float) $this->trips->sum('estimated_cost'));

                $sheet->getStyle('F' . $totalRow . ':G' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EDE9FE'],
                    ],
                ]);
                $sheet->getStyle('G' . $totalRow)->getNumberFormat()->setFormatCode('"Rp" #,##0');
            },
        ];
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
