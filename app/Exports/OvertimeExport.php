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
        return [
            1 => [
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6'],
                ],
            ],
        ];
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
