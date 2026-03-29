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
            if ($this->filters['status'] === 'late') {
                $query->where('is_late', true);
            } else {
                $query->where('status', $this->filters['status']);
            }
        }

        if (!empty($this->filters['department_id'])) {
            $departmentId = $this->filters['department_id'];
            $query->whereHas('worker', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if (!empty($this->filters['search'])) {
            $searchTerm = strtolower($this->filters['search']);
            $query->whereHas('worker', function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $searchTerm . '%']);
            });
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
            'Jam Masuk',
            'Jam Pulang',
            'Durasi Kerja',
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
        $workHours = '-';
        if ($attendance->check_in && $attendance->check_out) {
            $workHours = number_format($attendance->check_in->diffInHours($attendance->check_out, true), 1) . ' jam';
        }

        return [
            $attendance->attendance_date->format('d/m/Y'),
            $attendance->worker->nip ?? '-',
            $attendance->worker->name ?? '-',
            $attendance->shift->name ?? '-',
            config('attendance.location.name', '-'),
            $attendance->check_in ? $attendance->check_in->format('H:i:s') : '-',
            $attendance->check_out ? $attendance->check_out->format('H:i:s') : '-',
            $workHours,
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
        // Header styling dengan tema hijau modern
        $sheet->getStyle('A1:N1')->applyFromArray([
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

        // Row height untuk header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Data rows border
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle('A2:N' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);

            // Alternating row colors
            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':N' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F9FAFB');
                }
            }
        }

        return [];
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
