<?php

namespace App\Services\Dashboard;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Worker;
use Carbon\Carbon;

class EmployeeDashboardService
{
    /**
     * Get attendance summary for employee
     */
    public function getAttendanceSummary(string $workerId, string $period = 'month')
    {
        $query = Attendance::where('worker_id', $workerId);

        switch ($period) {
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

        // Use the internal status codes stored in the database
        $attendances = $query->get();

        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $late = $attendances->where('status', 'late')->count();
        $absent = $attendances->whereIn('status', ['absent', 'sick', 'permission', 'leave'])->count();

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Get attendance chart data (last 7 days)
     */
    public function getAttendanceChart(string $workerId, int $days = 7)
    {
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $attendance = Attendance::where('worker_id', $workerId)
                ->whereDate('attendance_date', $date)
                ->first();

            $labels[] = $date->format('d M');

            // Count presence for the day (present or late counted as hadir)
            if ($attendance && in_array($attendance->status, ['present', 'late'])) {
                $data[] = 1;
            } else {
                $data[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get leave requests summary
     */
    public function getLeaveSummary(string $workerId)
    {
        $currentYear = now()->year;
        $query = LeaveRequest::where('worker_id', $workerId)
            ->whereYear('start_date', $currentYear);

        $usedDays = (clone $query)->where('status', 'approved')->sum('total_days');

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'remaining_days' => max(0, 12 - $usedDays),
        ];
    }

    /**
     * Get overtime summary
     */
    public function getOvertimeSummary(string $workerId, string $period = 'month')
    {
        $query = OvertimeRequest::where('worker_id', $workerId);

        switch ($period) {
            case 'month':
                $query->whereMonth('overtime_date', now()->month)
                      ->whereYear('overtime_date', now()->year);
                break;
            case 'year':
                $query->whereYear('overtime_date', now()->year);
                break;
        }

        return [
            'total_requests' => (clone $query)->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'total_hours' => (clone $query)->where('status', 'approved')->sum('total_hours'),
        ];
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(string $workerId, int $limit = 5)
    {
        $activities = [];

        // Recent attendances
        $attendances = Attendance::where('worker_id', $workerId)
            ->latest('attendance_date')
            ->limit($limit)
            ->get()
            ->map(function($attendance) {
                // Map internal status codes to dashboard status badges and labels
                switch ($attendance->status) {
                    case 'present':
                        $badgeStatus = 'success';
                        $statusLabel = 'Hadir';
                        break;
                    case 'late':
                        $badgeStatus = 'late';
                        $statusLabel = 'Terlambat';
                        break;
                    case 'absent':
                    default:
                        $badgeStatus = 'absent';
                        $statusLabel = 'Tidak Hadir';
                        break;
                }

                return [
                    'type' => 'attendance',
                    'title' => 'Absensi ' . $statusLabel,
                    'description' => $attendance->check_in
                        ? 'Check-in pada ' . \Carbon\Carbon::parse($attendance->check_in)->format('H:i')
                        : 'Tidak ada data check-in',
                    'date' => $attendance->attendance_date,
                    'time' => \Carbon\Carbon::parse($attendance->created_at)->diffForHumans(),
                    'status' => $badgeStatus,
                    'icon' => 'fa-check-circle',
                    'color' => $attendance->status == 'present' ? 'green' : ($attendance->status == 'late' ? 'yellow' : 'red'),
                ];
            });

        // Recent leaves
        $leaves = LeaveRequest::where('worker_id', $workerId)
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function($leave) {
                return [
                    'type' => 'leave',
                    'title' => 'Permohonan Cuti',
                    'description' => ucfirst($leave->leaveType->name ?? 'Cuti') . ' - ' . \Carbon\Carbon::parse($leave->start_date)->format('d M Y'),
                    'date' => $leave->start_date,
                    'time' => \Carbon\Carbon::parse($leave->created_at)->diffForHumans(),
                    'status' => $leave->status == 'approved' ? 'success' : 'pending',
                    'icon' => 'fa-calendar-times',
                    'color' => $leave->status == 'approved' ? 'green' : ($leave->status == 'pending' ? 'yellow' : 'red'),
                ];
            });

        // Merge and sort
        $activities = $attendances->concat($leaves)
            ->sortByDesc('time')
            ->take($limit);

        return $activities->values();
    }

    /**
     * Get upcoming leaves
     */
    public function getUpcomingLeaves(string $workerId, int $limit = 5)
    {
        return LeaveRequest::where('worker_id', $workerId)
            ->where('status', 'approved')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get monthly attendance percentage
     */
    public function getMonthlyAttendancePercentage(string $workerId)
    {
        $totalDays = now()->day;
        $attendances = Attendance::where('worker_id', $workerId)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->count();

        return $totalDays > 0 ? round(($attendances / $totalDays) * 100, 1) : 0;
    }

    /**
     * Get leave balance/quota for employee
     */
    public function getLeaveBalance(string $workerId)
    {
        $currentYear = now()->year;

        // Get all leave types
        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->get();

        $balances = [];
        foreach ($leaveTypes as $leaveType) {
            // Count used days this year
            $usedDays = LeaveRequest::where('worker_id', $workerId)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->sum('total_days');

            // Default quota (you can customize this based on your business rules)
            $quota = $leaveType->max_days ?? 12; // Default 12 days if not set

            $balances[] = [
                'leave_type' => $leaveType->name,
                'quota' => $quota,
                'used' => $usedDays,
                'remaining' => max(0, $quota - $usedDays),
                'color' => $this->getLeaveTypeColor($leaveType->name),
            ];
        }

        return $balances;
    }

    /**
     * Get color for leave type
     */
    private function getLeaveTypeColor(string $typeName): string
    {
        $colors = [
            'Cuti Tahunan' => 'blue',
            'Cuti Sakit' => 'red',
            'Cuti Melahirkan' => 'pink',
            'Cuti Menikah' => 'purple',
            'Cuti Keluarga' => 'orange',
        ];

        return $colors[$typeName] ?? 'gray';
    }
}
