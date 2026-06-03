<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.employee|dashboard.hr|dashboard.manager');
    }

    public function index()
    {
        $user = Auth::user();

        // Get worker data from user relationship
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('login')
                ->with('error', 'Data pekerja tidak ditemukan. Silakan hubungi administrator.');
        }

        // Eager load department to avoid lazy loading in view
        $worker->load('department');

        // Get attendance summary
        $attendanceSummary = $this->getAttendanceSummary($worker->id, 'month');

        // Get attendance chart data
        $attendanceChart = $this->getAttendanceChart($worker->id, 7);

        // Get leave summary
        $leaveSummary = $this->getLeaveSummary($worker->id);

        // Get recent activities
        $recentActivities = $this->getRecentActivities($worker->id, 5);

        // Get upcoming leaves
        $upcomingLeaves = $this->getUpcomingLeaves($worker->id, 5);

        // Get recent leave requests
        $recentLeaves = LeaveRequest::with(['worker', 'leaveType', 'approver'])
            ->where('worker_id', $worker->id)
            ->latest('start_date')
            ->paginate(5);

        // Get leave balance with quota
        $leaveBalances = $this->getLeaveBalance($worker->id);

        // Split pending checkout into actionable vs expired window to avoid misleading CTA.
        $pendingCheckouts = $this->getPendingCheckouts($worker->id);
        $pendingCheckout = $pendingCheckouts->firstWhere('can_checkout', true);
        $expiredPendingCheckout = $pendingCheckouts->firstWhere('is_window_expired', true);

        return view('employee.dashboard.index', compact(
            'worker',
            'attendanceSummary',
            'attendanceChart',
            'leaveSummary',
            'recentActivities',
            'upcomingLeaves',
            'recentLeaves',
            'leaveBalances',
            'pendingCheckout',
            'expiredPendingCheckout'
        ));
    }

    private function getAttendanceSummary(string $workerId, string $period = 'month'): array
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

    private function getAttendanceChart(string $workerId, int $days = 7): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();

        $attendances = Attendance::where('worker_id', $workerId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($attendance) => Carbon::parse($attendance->attendance_date)->format('Y-m-d'));

        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');

            $attendance = $attendances->get($date->format('Y-m-d'));
            $data[] = ($attendance && in_array($attendance->status, ['present', 'late'], true)) ? 1 : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getLeaveSummary(string $workerId): array
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

    private function getRecentActivities(string $workerId, int $limit = 5)
    {
        $attendances = Attendance::where('worker_id', $workerId)
            ->latest('attendance_date')
            ->limit($limit)
            ->get()
            ->map(function ($attendance) {
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
                        ? 'Check-in pada ' . Carbon::parse($attendance->check_in)->format('H:i')
                        : 'Tidak ada data check-in',
                    'date' => $attendance->attendance_date,
                    'time' => Carbon::parse($attendance->created_at)->diffForHumans(),
                    'status' => $badgeStatus,
                    'icon' => 'fa-check-circle',
                    'color' => $attendance->status == 'present' ? 'green' : ($attendance->status == 'late' ? 'yellow' : 'red'),
                ];
            });

        $leaves = LeaveRequest::where('worker_id', $workerId)
            ->with('leaveType')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($leave) {
                return [
                    'type' => 'leave',
                    'title' => 'Permohonan Cuti',
                    'description' => ucfirst($leave->leaveType->name ?? 'Cuti') . ' - ' . Carbon::parse($leave->start_date)->format('d M Y'),
                    'date' => $leave->start_date,
                    'time' => Carbon::parse($leave->created_at)->diffForHumans(),
                    'status' => $leave->status == 'approved' ? 'success' : 'pending',
                    'icon' => 'fa-calendar-times',
                    'color' => $leave->status == 'approved' ? 'green' : ($leave->status == 'pending' ? 'yellow' : 'red'),
                ];
            });

        return $attendances->concat($leaves)
            ->sortByDesc('time')
            ->take($limit)
            ->values();
    }

    private function getUpcomingLeaves(string $workerId, int $limit = 5)
    {
        return LeaveRequest::where('worker_id', $workerId)
            ->where('status', 'approved')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit($limit)
            ->get();
    }

    private function getLeaveBalance(string $workerId): array
    {
        $currentYear = now()->year;
        $leaveTypes = LeaveType::where('is_active', true)->get();

        $usedByType = LeaveRequest::where('worker_id', $workerId)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->groupBy('leave_type_id')
            ->selectRaw('leave_type_id, SUM(total_days) as total')
            ->pluck('total', 'leave_type_id');

        $balances = [];
        foreach ($leaveTypes as $leaveType) {
            $usedDays = $usedByType->get($leaveType->id, 0);
            $quota = $leaveType->max_days ?? 12;

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

    private function getPendingCheckouts(?string $workerId = null, int $hoursThreshold = 0, bool $onlyActionable = false)
    {
        $now = now();

        $query = Attendance::with([
            'worker.department',
            'worker.workerShifts.shift',
            'worker.shiftOverrides.shift',
        ])
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->where('status', 'present');

        if ($workerId) {
            $query->where('worker_id', $workerId);
        }

        $pendingAttendances = $query->get();
        $pendingCheckouts = collect();

        foreach ($pendingAttendances as $attendance) {
            $worker = $attendance->worker;
            if (!$worker) {
                continue;
            }

            $attendanceDate = Carbon::parse($attendance->attendance_date);

            $shiftOverride = $worker->shiftOverrides->first(function ($override) use ($attendanceDate) {
                $overrideDate = $override->override_date instanceof Carbon
                    ? $override->override_date->format('Y-m-d')
                    : $override->override_date;
                return $overrideDate === $attendanceDate->format('Y-m-d');
            });

            $shift = null;
            if ($shiftOverride && $shiftOverride->shift) {
                $shift = $shiftOverride->shift;
            } else {
                $activeShift = $worker->workerShifts->first(function ($workerShift) use ($attendanceDate) {
                    return $workerShift->isActiveOnDate($attendanceDate);
                });

                if ($activeShift) {
                    $shift = $activeShift->shift;
                }
            }

            if (!$shift) {
                continue;
            }

            $schedule = $shift->getScheduleForDate($attendanceDate);
            $shiftEndTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $schedule['end_time']);

            if ($schedule['is_overnight']) {
                $shiftEndTime->addDay();
            }

            $thresholdTime = $shiftEndTime->copy()->addHours($hoursThreshold);

            if ($now->greaterThan($thresholdTime)) {
                $hoursLate = $now->diffInHours($shiftEndTime);

                $checkOutWindowAfterMinutes = (int) round((float) config('attendance.check_out_window_after_hours', 1.5) * 60);
                $maxCheckoutTime = $shiftEndTime->copy()->addMinutes($checkOutWindowAfterMinutes);
                $isWindowExpired = $now->greaterThan($maxCheckoutTime);

                if ($onlyActionable && $isWindowExpired) {
                    continue;
                }

                $pendingCheckouts->push([
                    'attendance_id' => $attendance->id,
                    'worker_id' => $worker->id,
                    'worker_name' => $worker->name,
                    'position' => $worker->department->name ?? '-',
                    'attendance_date' => $attendanceDate->format('Y-m-d'),
                    'check_in_time' => Carbon::parse($attendance->check_in)->format('H:i'),
                    'shift_name' => $shift->name,
                    'shift_end_time' => $shiftEndTime->format('Y-m-d H:i'),
                    'hours_late' => $hoursLate,
                    'formatted_late' => $this->formatHoursLate($hoursLate),
                    'max_checkout_time' => $maxCheckoutTime->format('Y-m-d H:i'),
                    'is_window_expired' => $isWindowExpired,
                    'can_checkout' => !$isWindowExpired,
                ]);
            }
        }

        return $pendingCheckouts->sortByDesc('hours_late');
    }

    private function formatHoursLate(int $hours): string
    {
        if ($hours < 1) {
            return 'Baru berakhir';
        }

        if ($hours < 24) {
            return $hours . ' jam yang lalu';
        }

        $days = floor($hours / 24);
        $remainingHours = $hours % 24;
        if ($remainingHours > 0) {
            return $days . ' hari ' . $remainingHours . ' jam yang lalu';
        }

        return $days . ' hari yang lalu';
    }
}
