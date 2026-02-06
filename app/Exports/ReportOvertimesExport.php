<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ReportOvertimesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            'Tanggal Lembur',
            'Waktu Mulai',
            'Waktu Selesai',
            'Durasi (Jam)',
            'Alasan',
            'Status',
            'Disetujui Oleh',
        ];
    }

    public function map($overtime): array
    {
        $startTime = $overtime->start_time ? \Carbon\Carbon::parse($overtime->start_time) : null;
        $endTime = $overtime->end_time ? \Carbon\Carbon::parse($overtime->end_time) : null;

        return [
            $overtime->created_at?->format('Y-m-d') ?? '-',
            $overtime->worker->nip ?? '-',
            $overtime->worker->name ?? '-',
            $overtime->overtime_date?->format('Y-m-d') ?? '-',
            $startTime?->format('H:i') ?? '-',
            $endTime?->format('H:i') ?? '-',
            $overtime->total_hours ?? 0,
            $overtime->reason ?? '-',
            $this->getStatusLabel($overtime->status),
            $overtime->approver->name ?? '-',
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
