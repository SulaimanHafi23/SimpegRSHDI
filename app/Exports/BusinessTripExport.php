<?php

namespace App\Exports;

use App\Models\BusinessTrip;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BusinessTripExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = BusinessTrip::with(['worker.department', 'approvedBy']);

        if (!empty($this->filters['worker_id'])) {
            $query->where('worker_id', $this->filters['worker_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('start_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('start_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Department filter for Manager role
        if (!empty($this->filters['department_id'])) {
            $query->whereHas('worker', function ($q) {
                $q->where('department_id', $this->filters['department_id']);
            });
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Pegawai',
            'Departemen',
            'Tujuan',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi (Hari)',
            'Estimasi Biaya',
            'Status',
            'Disetujui Oleh',
            'Tujuan Perjalanan',
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
            $trip->worker->nip ?? '-',
            $trip->worker->name ?? '-',
            $trip->worker->department->name ?? '-',
            $trip->destination ?? '-',
            $trip->start_date?->format('d/m/Y') ?? '-',
            $trip->end_date?->format('d/m/Y') ?? '-',
            $duration,
            $trip->estimated_cost ? 'Rp ' . number_format($trip->estimated_cost, 0, ',', '.') : '-',
            $this->getStatusLabel($trip->status),
            $trip->approvedBy->name ?? '-',
            $trip->purpose ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '8B5CF6'],
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
