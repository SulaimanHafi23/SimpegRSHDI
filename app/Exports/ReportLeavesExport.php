<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ReportLeavesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $filters;

    public function __construct(Collection $data, array $filters = [])
    {
        $this->data = $data;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Tanggal Pengajuan',
            'NIP',
            'Nama Pegawai',
            'Jenis Cuti',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi (Hari)',
            'Alasan',
            'Status',
            'Disetujui Oleh',
        ];
    }

    public function map($leave): array
    {
        return [
            $leave->created_at?->format('Y-m-d') ?? '-',
            $leave->worker->nip ?? '-',
            $leave->worker->name ?? '-',
            $leave->leaveType->name ?? '-',
            $leave->start_date?->format('Y-m-d') ?? '-',
            $leave->end_date?->format('Y-m-d') ?? '-',
            $leave->total_days ?? 0,
            $leave->reason ?? '-',
            $this->getStatusLabel($leave->status),
            $leave->approver->name ?? '-',
        ];
    }

    protected function getStatusLabel($status): string
    {
        return match($status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($status ?? '-'),
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16A34A'],
                ],
            ],
        ];
    }
}
