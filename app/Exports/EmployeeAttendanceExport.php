<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class EmployeeAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $attendances;
    protected $worker;
    protected int $rowNumber = 0;

    public function __construct(Collection $attendances, $worker)
    {
        $this->attendances = $attendances;
        $this->worker = $worker;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Hari',
            'Nama Shift',
            'Jadwal Shift',
            'Check In',
            'Check Out',
            'Status',
            'Terlambat',
            'Pulang Cepat',
            'Lokasi',
            'Keterangan',
        ];
    }

    public function map($attendance): array
    {
        $this->rowNumber++;

        $attendanceDate = $attendance->attendance_date;
        $shift = $attendance->shift;

        $shiftSchedule = '-';
        if ($shift && $attendanceDate) {
            $schedule = $shift->getScheduleForDate($attendanceDate);
            $shiftSchedule = sprintf(
                '%s - %s',
                \Carbon\Carbon::parse($schedule['start_time'])->format('H:i'),
                \Carbon\Carbon::parse($schedule['end_time'])->format('H:i')
            );
        }

        $status = match($attendance->status) {
            'present' => $attendance->is_late ? 'Terlambat' : 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'leave' => 'Cuti',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => ucfirst($attendance->status ?? '-')
        };

        $lateInfo = ($attendance->is_late && (int) $attendance->late_minutes > 0)
            ? ((int) $attendance->late_minutes . ' menit')
            : '-';

        $earlyLeaveInfo = ($attendance->is_early_leave && (int) $attendance->early_leave_minutes > 0)
            ? ((int) $attendance->early_leave_minutes . ' menit')
            : '-';

        return [
            $this->rowNumber,
            $attendanceDate?->format('d/m/Y') ?? '-',
            $attendanceDate?->translatedFormat('l') ?? '-',
            $shift?->name ?? '-',
            $shiftSchedule,
            $attendance->check_in?->format('H:i') ?? '-',
            $attendance->check_out?->format('H:i') ?? '-',
            $status,
            $lateInfo,
            $earlyLeaveInfo,
            config('attendance.location.name', '-'),
            $attendance->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set header style
        $sheet->getStyle('A1:L1')->applyFromArray([
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

        // Set all cells border
        $lastRow = $this->attendances->count() + 1;
        $sheet->getStyle('A1:L' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Zebra striping
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ],
                ]);
            }
        }

        $sheet->freezePane('A2');

        return [];
    }
}
