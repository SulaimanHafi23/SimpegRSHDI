<?php

namespace App\Exports;

use App\Models\OvertimeRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OvertimesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = OvertimeRequest::with(['worker', 'approver']);

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

        return $query->orderBy('overtime_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Jam Mulai',
            'Jam Selesai',
            'Total Jam',
            'NIP',
            'Nama Pegawai',
            'Alasan',
            'Status',
            'Disetujui Oleh',
            'Tanggal Persetujuan',
            'Alasan Penolakan',
        ];
    }

    public function map($ot): array
    {
        return [
            $ot->overtime_date?->format('d/m/Y') ?? '-',
            $ot->start_time?->format('H:i') ?? '-',
            $ot->end_time?->format('H:i') ?? '-',
            $ot->total_hours ?? '-',
            $ot->worker->nip ?? '-',
            $ot->worker->name ?? '-',
            $ot->reason ?? '-',
            ucfirst($ot->status),
            $ot->approver->name ?? '-',
            $ot->approved_at?->format('d/m/Y H:i') ?? '-',
            $ot->rejection_reason ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2196F3']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}
