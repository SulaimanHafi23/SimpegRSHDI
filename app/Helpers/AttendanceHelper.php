<?php

namespace App\Helpers;

use App\Models\Attendance;
use Illuminate\Support\Collection;

class AttendanceHelper
{
    /**
     * Get monthly attendance summary for a worker
     *
     * @param string $workerId
     * @param int $month
     * @param int $year
     * @return Collection
     */
    public static function getMonthlyReport(string $workerId, int $month, int $year): Collection
    {
        return Attendance::where('user_id', $workerId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get()
            ->groupBy('status');
    }

    /**
     * Get attendance summary statistics
     *
     * @param string $workerId
     * @param int $month
     * @param int $year
     * @return array
     */
    public static function getSummaryStats(string $workerId, int $month, int $year): array
    {
        $summary = self::getMonthlyReport($workerId, $month, $year);

        return [
            'hadir' => $summary->get('present', collect())->count(),
            'terlambat' => $summary->get('late', collect())->count(),
            'tidakHadir' => collect($summary->get('absent', []))
                ->merge($summary->get('sick', []))
                ->merge($summary->get('permission', []))
                ->merge($summary->get('leave', []))
                ->count(),
            'izin' => $summary->get('permission', collect())->count(),
            'sakit' => $summary->get('sick', collect())->count(),
            'cuti' => $summary->get('leave', collect())->count(),
        ];
    }
}
