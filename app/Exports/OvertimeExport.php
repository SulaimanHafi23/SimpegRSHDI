<?php

namespace App\Exports;

use App\Models\OvertimeRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OvertimeExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = OvertimeRequest::with(['worker.department', 'approver']);

        if (!empty($this->filters['worker_id'])) {
            $query->where('worker_id', $this->filters['worker_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('overtime_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('overtime_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['department_id'])) {
            $query->whereHas('worker', function ($q) {
                $q->where('department_id', $this->filters['department_id']);
            });
        }

        return $query->orderBy('overtime_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Pegawai',
            'Departemen',
            'Tanggal Lembur',
            'Jam Mulai',
            'Jam Selesai',
            'Total Jam',
            'Status',
            'Disetujui Oleh',
            'Alasan',
        ];
    }

    public function map($overtime): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $overtime->worker->nip ?? '-',
            $overtime->worker->name ?? '-',
            $overtime->worker->department->name ?? '-',
            $overtime->overtime_date?->format('d/m/Y') ?? '-',
            $overtime->start_time ? \Carbon\Carbon::parse($overtime->start_time)->format('H:i') : '-',
            $overtime->end_time ? \Carbon\Carbon::parse($overtime->end_time)->format('H:i') : '-',
            $overtime->total_hours ?? '-',
            $this->getStatusLabel($overtime->status),
            $overtime->approver->name ?? '-',
            $overtime->reason ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling dengan tema hijau modern
        $sheet->getStyle('A1:K1')->applyFromArray([
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

        $sheet->getRowDimension(1)->setRowHeight(25);

        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle('A2:K' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);

            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':K' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('DBEAFE');
                }
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
            default => $status,
        };
    }
}
