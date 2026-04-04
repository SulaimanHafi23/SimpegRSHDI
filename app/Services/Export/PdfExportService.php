<?php

namespace App\Services\Export;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    /**
     * Export attendance report to PDF
     */
    public function exportAttendanceReport($attendances, $worker, $filters)
    {
        try {
            $collection = collect($attendances);
            $rows = $collection
                ->sortBy('attendance_date')
                ->values()
                ->map(function ($attendance) {
                    $attendanceDate = $attendance->attendance_date;
                    $shift = $attendance->shift;

                    $shiftTime = '-';
                    if ($shift && $attendanceDate) {
                        $schedule = $shift->getScheduleForDate($attendanceDate);
                        $shiftTime = sprintf(
                            '%s - %s',
                            \Carbon\Carbon::parse($schedule['start_time'])->format('H:i'),
                            \Carbon\Carbon::parse($schedule['end_time'])->format('H:i')
                        );
                    }

                    $status = match ($attendance->status) {
                        'present' => $attendance->is_late ? 'Terlambat' : 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'leave' => 'Cuti',
                        'sick' => 'Sakit',
                        'permission' => 'Izin',
                        default => ucfirst($attendance->status ?? '-'),
                    };

                    return [
                        'date' => $attendanceDate ? $attendanceDate->format('d/m/Y') : '-',
                        'day_name' => $attendanceDate ? $attendanceDate->translatedFormat('l') : '-',
                        'shift_name' => $shift?->name ?? '-',
                        'shift_time' => $shiftTime,
                        'check_in' => $attendance->check_in ? $attendance->check_in->format('H:i:s') : '-',
                        'check_out' => $attendance->check_out ? $attendance->check_out->format('H:i:s') : '-',
                        'status' => $status,
                        'late' => ($attendance->is_late && (int) $attendance->late_minutes > 0)
                            ? ((int) $attendance->late_minutes . ' menit')
                            : '-',
                        'early_leave' => ($attendance->is_early_leave && (int) $attendance->early_leave_minutes > 0)
                            ? ((int) $attendance->early_leave_minutes . ' menit')
                            : '-',
                        'location' => config('attendance.location.name', '-'),
                        'notes' => $attendance->notes ?: '-',
                    ];
                })
                ->all();

            $data = [
                'title' => 'Laporan Riwayat Absensi',
                'worker' => $worker,
                'rows' => $rows,
                'startDate' => \Carbon\Carbon::parse($filters['date_from']),
                'endDate' => \Carbon\Carbon::parse($filters['date_to']),
                'filters' => $filters,
                'generated_at' => now()->format('d F Y H:i'),
                'summary' => [
                    'total' => $collection->count(),
                    'present' => $collection->where('status', 'present')->count(),
                    'late' => $collection->where('is_late', true)->count(),
                    'absent' => $collection->where('status', 'absent')->count(),
                    'leave' => $collection->where('status', 'leave')->count(),
                    'sick' => $collection->where('status', 'sick')->count(),
                    'permission' => $collection->where('status', 'permission')->count(),
                ]
            ];

            $pdf = Pdf::loadView('employee.exports.attendance-pdf', $data);
            $pdf->setPaper('a4', 'portrait');

            $filename = 'Absensi_' . $worker->name . '_' . now()->format('YmdHis') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            // If GD extension is not available or other PDF error occurs
            if (strpos($e->getMessage(), 'GD extension') !== false) {
                throw new \Exception('Error: PHP GD Extension tidak ter-install. Hubungi administrator server untuk install PHP GD extension.');
            }
            throw $e;
        }
    }

    /**
     * Export leave report to PDF
     */
    public function exportLeaveReport($leaves, $worker, $filters)
    {
        try {
            $collection = collect($leaves);

            $data = [
                'title' => 'Laporan Riwayat Cuti',
                'worker' => $worker,
                'leaves' => $leaves,
                'filters' => $filters,
                'generated_at' => now()->format('d F Y H:i'),
                'summary' => [
                    'total' => $collection->count(),
                    'pending' => $collection->where('status', 'pending')->count(),
                    'approved' => $collection->where('status', 'approved')->count(),
                    'rejected' => $collection->where('status', 'rejected')->count(),
                ]
            ];

            $pdf = Pdf::loadView('employee.exports.leave-pdf', $data);
            $pdf->setPaper('a4', 'portrait');

            $filename = 'Cuti_' . $worker->name . '_' . now()->format('YmdHis') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'GD extension') !== false) {
                throw new \Exception('Error: PHP GD Extension tidak ter-install. Hubungi administrator server untuk install PHP GD extension.');
            }
            throw $e;
        }
    }

}
