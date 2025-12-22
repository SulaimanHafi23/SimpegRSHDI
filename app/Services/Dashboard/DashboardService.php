<?php

namespace App\Services\Dashboard;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get total workers count
     */
    public function getTotalWorkers(): int
    {
        return Worker::count();
    }

    /**
     * Get total active workers (have attendance this month)
     */
    public function getActiveWorkers(): int
    {
        return Worker::whereHas('attendances', function($query) {
            $query->whereMonth('attendance_date', now()->month)
                  ->whereYear('attendance_date', now()->year);
        })->count();
    }

    /**
     * Get attendance statistics for a period
     */
    public function getAttendanceStats(string $period = 'today')
    {
        $query = Attendance::query();

        switch ($period) {
            case 'today':
                $query->whereDate('attendance_date', now());
                break;
            case 'week':
                $query->whereBetween('attendance_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('attendance_date', now()->month)
                      ->whereYear('attendance_date', now()->year);
                break;
            case 'year':
                $query->whereYear('attendance_date', now()->year);
                break;
        }

        return [
            'total' => $query->count(),
            'on_time' => $query->where('status', 'Hadir')->count(),
            'late' => $query->where('status', 'Terlambat')->count(),
            'absent' => $query->where('status', 'Tidak Hadir')->count(),
        ];
    }

    /**
     * Get attendance chart data for last 7 days
     */
    public function getAttendanceChartData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Attendance::whereDate('attendance_date', $date)->count();
            $data[] = [
                'date' => $date->format('d M'),
                'count' => $count
            ];
        }
        return $data;
    }

    /**
     * Get monthly attendance chart (last 6 months)
     */
    public function getMonthlyAttendanceChart()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Attendance::whereMonth('attendance_date', $date->month)
                               ->whereYear('attendance_date', $date->year)
                               ->count();
            $data[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
        return $data;
    }

    /**
     * Get leave request statistics
     */
    public function getLeaveStats(string $period = 'month')
    {
        $query = LeaveRequest::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', now());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        return [
            'total' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'approved' => $query->where('status', 'approved')->count(),
            'rejected' => $query->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Get overtime request statistics
     */
    public function getOvertimeStats(string $period = 'month')
    {
        $query = OvertimeRequest::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', now());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        return [
            'total' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'approved' => $query->where('status', 'approved')->count(),
            'rejected' => $query->where('status', 'rejected')->count(),
            'total_hours' => $query->where('status', 'approved')->sum('total_hours'),
        ];
    }

    /**
     * Get top 10 most active workers (by attendance)
     */
    public function getTopWorkers(int $limit = 10)
    {
        return Worker::withCount(['attendances' => function($query) {
                $query->whereMonth('attendance_date', now()->month)
                      ->whereYear('attendance_date', now()->year);
            }])
            ->orderBy('attendances_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get attendance by status chart data
     */
    public function getAttendanceByStatusChart()
    {
        return [
            'on_time' => Attendance::where('status', 'Hadir')
                ->whereMonth('attendance_date', now()->month)
                ->count(),
            'late' => Attendance::where('status', 'Terlambat')
                ->whereMonth('attendance_date', now()->month)
                ->count(),
            'absent' => Attendance::where('status', 'Tidak Hadir')
                ->whereMonth('attendance_date', now()->month)
                ->count(),
        ];
    }

    /**
     * Get pending approvals count
     */
    public function getPendingApprovalsCount()
    {
        return [
            'leaves' => LeaveRequest::where('status', 'pending')->count(),
            'overtimes' => OvertimeRequest::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get recent activities (latest attendances, leaves, overtimes)
     */
    public function getRecentActivities(int $limit = 10)
    {
        $activities = [];

        // Recent attendances
        $attendances = Attendance::with('worker')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function($attendance) {
                return [
                    'type' => 'attendance',
                    'title' => $attendance->worker->name . ' check-in',
                    'description' => 'Status: ' . $attendance->status,
                    'time' => $attendance->created_at,
                ];
            });

        // Recent leaves
        $leaves = LeaveRequest::with('worker')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function($leave) {
                return [
                    'type' => 'leave',
                    'title' => $leave->worker->name . ' mengajukan cuti',
                    'description' => 'Status: ' . ucfirst($leave->status),
                    'time' => $leave->created_at,
                ];
            });

        // Merge and sort
        $activities = $attendances->concat($leaves)
            ->sortByDesc('time')
            ->take($limit);

        return $activities->values();
    }
}
