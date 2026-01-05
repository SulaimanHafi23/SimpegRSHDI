<?php

namespace App\Exports;

use App\Models\LeaveRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeavesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = LeaveRequest::with(['worker', 'leaveType', 'approver']);

        if (!empty($this->filters['worker_id'])) {
            $query->where('worker_id', $this->filters['worker_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('start_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('end_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Total Hari',
            'NIP',
            'Nama Pegawai',
            'Tipe Cuti',
            'Alasan',
            'Status',
            'Disetujui Oleh',
            'Tanggal Persetujuan',
            'Alasan Penolakan',
        ];
    }

    public function map($leave): array
    {
        return [
            $leave->start_date?->format('d/m/Y') ?? '-',
            $leave->end_date?->format('d/m/Y') ?? '-',
            $leave->total_days ?? 0,
            $leave->worker->nip ?? '-',
            $leave->worker->name ?? '-',
            $leave->leaveType->name ?? '-',
            $leave->reason ?? '-',
            ucfirst($leave->status),
            $leave->approver->name ?? '-',
            $leave->approved_at?->format('d/m/Y H:i') ?? '-',
            $leave->rejection_reason ?? '-',
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
