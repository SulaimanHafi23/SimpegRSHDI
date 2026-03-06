<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class EmployeeShiftSwapExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $swaps;
    protected $worker;

    public function __construct(Collection $swaps, $worker)
    {
        $this->swaps = $swaps;
        $this->worker = $worker;
    }

    public function collection()
    {
        return $this->swaps;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Pengajuan',
            'Tanggal Tukar',
            'Tipe',
            'Pemohon',
            'Shift Pemohon',
            'Target',
            'Shift Target',
            'Status',
            'Alasan',
        ];
    }

    public function map($swap): array
    {
        static $no = 0;
        $no++;

        $status = match($swap->status) {
            'pending' => 'Menunggu',
            'accepted' => 'Diterima',
            'awaiting_approval' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'executed' => 'Dieksekusi',
            default => ucfirst($swap->status ?? '-')
        };

        $swapType = match($swap->swap_type) {
            'direct' => 'Langsung',
            'open' => 'Open Request',
            default => ucfirst($swap->swap_type ?? '-')
        };

        return [
            $no,
            $swap->created_at?->format('d/m/Y') ?? '-',
            $swap->swap_date?->format('d/m/Y') ?? '-',
            $swapType,
            $swap->requester->name ?? '-',
            $swap->requesterShift->shift->name ?? '-',
            $swap->targetWorker->name ?? '-',
            $swap->targetShift->shift->name ?? '-',
            $status,
            $swap->reason ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
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

        $lastRow = $this->swaps->count() + 1;
        $sheet->getStyle('A1:J' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
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
