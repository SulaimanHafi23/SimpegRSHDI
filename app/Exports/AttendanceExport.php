<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Attendance::with(['worker', 'shift', 'location']);

        // Apply filters
        if (!empty($this->filters['worker_id'])) {
            $query->where('worker_id', $this->filters['worker_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('attendance_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('attendance_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('attendance_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIP',
            'Nama Pegawai',
            'Shift',
            'Lokasi',
            'Check In',
            'Check Out',
            'Jam Kerja',
            'Status',
            'Terlambat',
            'Menit Terlambat',
            'Pulang Cepat',
            'Menit Pulang Cepat',
            'Catatan',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->attendance_date->format('d/m/Y'),
            $attendance->worker->nip ?? '-',
            $attendance->worker->name ?? '-',
            $attendance->shift->name ?? '-',
            $attendance->location->name ?? '-',
            $attendance->check_in ? $attendance->check_in->format('H:i:s') : '-',
            $attendance->check_out ? $attendance->check_out->format('H:i:s') : '-',
            $attendance->work_hours ?? '-',
            $this->getStatusLabel($attendance->status),
            $attendance->is_late ? 'Ya' : 'Tidak',
            $attendance->late_minutes ?? 0,
            $attendance->is_early_leave ? 'Ya' : 'Tidak',
            $attendance->early_leave_minutes ?? 0,
            $attendance->notes ?? '-',
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

    protected function getStatusLabel($status)
    {
        return match($status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'leave' => 'Cuti',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => $status
        };
    }
}
