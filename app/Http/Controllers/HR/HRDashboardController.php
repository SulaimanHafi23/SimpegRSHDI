<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\WorkerDocument;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HRDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HR');
    }

    public function index()
    {
        // ========== WORKER STATISTICS ==========
        $workerStats = Worker::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
            SUM(CASE WHEN status = 'resigned' THEN 1 ELSE 0 END) as resigned,
            SUM(CASE WHEN employment_status = 'permanent' THEN 1 ELSE 0 END) as permanent,
            SUM(CASE WHEN employment_status = 'contract' THEN 1 ELSE 0 END) as contract,
            SUM(CASE WHEN employment_status = 'probation' THEN 1 ELSE 0 END) as probation,
            SUM(CASE WHEN employment_status = 'intern' THEN 1 ELSE 0 END) as intern
        ")->first();

        $totalWorkers = $workerStats->total;
        $activeWorkers = $workerStats->active;
        $inactiveWorkers = $workerStats->inactive;
        $resignedWorkers = $workerStats->resigned;
        $permanentWorkers = $workerStats->permanent;
        $contractWorkers = $workerStats->contract;
        $probationWorkers = $workerStats->probation;
        $internWorkers = $workerStats->intern;

        // Workers by Department
        $workersByDepartment = Worker::select('department_id', DB::raw('count(*) as total'))
            ->with('department')
            ->groupBy('department_id')
            ->get()
            ->map(function ($item) {
                return [
                    'department' => $item->department->name ?? 'N/A',
                    'total' => $item->total
                ];
            });

        // ========== ATTENDANCE TODAY ==========
        $today = now()->format('Y-m-d');
        $attendanceToday = Attendance::whereDate('attendance_date', $today)->count();
        $lateToday = Attendance::whereDate('attendance_date', $today)
            ->where('is_late', true)
            ->count();
        $absentToday = $activeWorkers - $attendanceToday;

        // ========== LEAVE REQUESTS ==========
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $approvedLeavesThisMonth = LeaveRequest::where('status', 'approved')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->count();

        // Recent Leave Requests
        $recentLeaves = LeaveRequest::with(['worker', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // ========== DOCUMENTS ==========
        $pendingDocuments = WorkerDocument::where('status', 'pending')->count();
        $verifiedDocumentsThisMonth = WorkerDocument::where('status', 'verified')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ========== ATTENDANCE CHART (Last 7 Days) ==========
        $startDate = now()->subDays(6)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $chartData = Attendance::selectRaw("
            attendance_date,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_count,
            COUNT(*) as total_count
        ")
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->groupBy('attendance_date')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->attendance_date)->format('Y-m-d');
            });

        $attendanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dayName = $date->format('D');
            $dayData = $chartData->get($dateStr);

            $attendanceChart[] = [
                'date' => $dayName,
                'present' => $dayData->present_count ?? 0,
                'late' => $dayData->late_count ?? 0,
                'absent' => $activeWorkers - ($dayData->total_count ?? 0),
            ];
        }

        // ========== RECENT ACTIVITIES ==========
        $recentActivities = collect([]);

        // Add recent hires
        $recentHires = Worker::where('status', 'active')
            ->latest('hire_date')
            ->take(3)
            ->get()
            ->map(function ($worker) {
                return [
                    'type' => 'worker_joined',
                    'icon' => 'user-plus',
                    'color' => 'blue',
                    'title' => 'Pegawai Baru',
                    'description' => $worker->name . ' bergabung sebagai ' . ($worker->employment_status ?? 'Pegawai'),
                    'time' => $worker->hire_date?->diffForHumans() ?? '-',
                ];
            });

        // Add recent resignations
        $recentResignations = Worker::where('status', 'resigned')
            ->whereNotNull('resign_date')
            ->latest('resign_date')
            ->take(2)
            ->get()
            ->map(function ($worker) {
                return [
                    'type' => 'worker_resigned',
                    'icon' => 'user-minus',
                    'color' => 'red',
                    'title' => 'Pegawai Resign',
                    'description' => $worker->name . ' telah resign',
                    'time' => $worker->resign_date->diffForHumans(),
                ];
            });

        $recentActivities = $recentHires->concat($recentResignations)
            ->sortByDesc('time')
            ->take(5)
            ->values();

        // ========== PENDING CHECKOUTS ==========
        $pendingCheckouts = $this->getPendingCheckouts();

        return view('hr.dashboard.index', compact(
            'totalWorkers',
            'activeWorkers',
            'inactiveWorkers',
            'resignedWorkers',
            'permanentWorkers',
            'contractWorkers',
            'probationWorkers',
            'internWorkers',
            'workersByDepartment',
            'attendanceToday',
            'lateToday',
            'absentToday',
            'pendingLeaves',
            'approvedLeavesThisMonth',
            'recentLeaves',
            'pendingDocuments',
            'verifiedDocumentsThisMonth',
            'attendanceChart',
            'recentActivities',
            'pendingCheckouts'
        ));
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
