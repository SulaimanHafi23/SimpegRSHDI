<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftOverride;
use App\Models\WorkerShift;
use App\Models\WorkerShiftHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display employee's shift schedule
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        // Get month and year from request or use current
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $date = Carbon::create($year, $month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Get national holidays for this month
        $holidays = \App\Models\Holiday::where('is_national', true)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function($holiday) {
                return Carbon::parse($holiday->date)->format('Y-m-d');
            });

        // Get all worker shifts (including inactive ones if they fall in range)
        $workerShifts = WorkerShift::with(['worker', 'shift'])
            ->where('worker_id', $worker->id)
            ->get();

        // Get shift history for this worker to fill past gaps in calendar
        $shiftHistories = WorkerShiftHistory::where('worker_id', $worker->id)
            ->with('shift')
            ->get();

        // Merge current shifts and histories for calendar building
        $allHistoricalShifts = $shiftHistories->map(function($history) {
            return (object)[
                'shift_id' => $history->shift_id,
                'shift' => $history->shift,
                'effective_from' => $history->effective_from,
                'effective_until' => $history->effective_until,
                'is_active' => true, // Treat as active for calendar display
                'is_history' => true
            ];
        });

        $combinedShifts = $workerShifts->concat($allHistoricalShifts);

        // Get shift overrides for this month
        $shiftOverrides = ShiftOverride::with(['worker', 'shift', 'creator'])
            ->where('worker_id', $worker->id)
            ->where('override_date', '>=', $startOfMonth->format('Y-m-d'))
            ->where('override_date', '<=', $endOfMonth->format('Y-m-d'))
            ->latest('override_date')
            ->get();

        // Get approved leave requests for this month
        $leaveRequests = \App\Models\LeaveRequest::with('leaveType')
            ->where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $endOfMonth->format('Y-m-d'))
            ->whereDate('end_date', '>=', $startOfMonth->format('Y-m-d'))
            ->get();

        // Build calendar data using combined shifts
        $requiresHolidayAttendance = $worker->department && $worker->department->requires_holiday_attendance;
        $calendar = $this->buildCalendar($worker, $startOfMonth, $endOfMonth, $combinedShifts, $shiftOverrides, $holidays, $leaveRequests, $requiresHolidayAttendance);

        // Urutkan shift dari yang terbaru (effective_from descending) untuk penentuan info header
        $sortedShifts = collect($workerShifts)->sortByDesc('effective_from');

        // Determine current active shift using service helper (more reliable)
        $workerShift = WorkerShift::where('worker_id', $worker->id)
            ->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('created_at')
            ->with(['shift'])
            ->first();

        // FALLBACK: Jika tidak ada jadwal aktif saat ini, ambil jadwal terakhir (terbaru)
        if (!$workerShift && count($workerShifts) > 0) {
            $workerShift = $sortedShifts->first();
        }

        // Get shift history for this worker
        $shiftHistories = WorkerShiftHistory::where('worker_id', $worker->id)
            ->with('shift', 'changedByUser')
            ->orderByDesc('changed_at')
            ->orderByDesc('created_at')
            ->get();

        return view('employee.shifts.index', compact(
            'calendar',
            'workerShift',
            'shiftHistories',
            'date',
            'month',
            'year'
        ));
    }

    /**
     * Show shift detail for specific date
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $date = Carbon::parse($request->date);

        // Get worker shift for specific date
        $workerShifts = WorkerShift::with(['worker', 'shift'])
            ->where('worker_id', $worker->id)
            ->latest()
            ->get();

        $workerShift = collect($workerShifts)->first(function ($shift) use ($date) {
            // Use model helpers to check if this worker shift applies to the requested date
            if (!($shift->is_active ?? false)) return false;
            if (!method_exists($shift, 'isActiveOnDate') || !method_exists($shift, 'getShiftForDate')) {
                return false;
            }

            if (!$shift->isActiveOnDate($date->toDateTime())) return false;
            $shiftId = $shift->getShiftForDate($date->toDateTime());
            return !empty($shiftId);
        });

        // Check for override on this date
        $override = ShiftOverride::with(['worker', 'shift', 'creator'])
            ->where('worker_id', $worker->id)
            ->where('override_date', $date->format('Y-m-d'))
            ->latest('override_date')
            ->first();

        return view('employee.shifts.show', compact(
            'date',
            'workerShift',
            'override'
        ));
    }

    /**
     * Build calendar array with shift information
     */
    private function buildCalendar($worker, $startOfMonth, $endOfMonth, $workerShifts, $shiftOverrides, $holidays, $leaveRequests, $requiresHolidayAttendance = false)
    {
        $calendar = [];
        $current = $startOfMonth->copy();

        // Urutkan shift dari yang terbaru (effective_from descending)
        // Agar jika ada tumpang tindih, jadwal terbaru yang dipakai
        $sortedShifts = collect($workerShifts)->sortByDesc('effective_from');

        // Start from the first day of the week containing the 1st (Sunday)
        $current->startOfWeek(Carbon::SUNDAY);

        // Build 6 weeks (42 days) to ensure full calendar
        for ($week = 0; $week < 6; $week++) {
            $calendar[$week] = [];

            for ($day = 0; $day < 7; $day++) {
                $dayData = [
                    'date' => $current->copy(),
                    'isCurrentMonth' => $current->month == $startOfMonth->month,
                    'isToday' => $current->isToday(),
                    'shift' => null,
                    'schedule' => null,
                    'isOverride' => false,
                    'isOffDay' => false,
                    'isLeave' => false,
                    'leaveTypeName' => null,
                    'isHoliday' => false,
                    'holidayName' => null,
                ];

                // Check approved leave first
                $leaveRequest = $leaveRequests->first(function ($leave) use ($current) {
                    return $current->between(
                        Carbon::parse($leave->start_date)->startOfDay(),
                        Carbon::parse($leave->end_date)->endOfDay()
                    );
                });

                if ($leaveRequest) {
                    $dayData['isLeave'] = true;
                    $dayData['leaveTypeName'] = $leaveRequest->leaveType->name ?? 'Cuti';
                    $calendar[$week][] = $dayData;
                    $current->addDay();
                    continue;
                }

                // Check worker personal off-day first (exception/pattern)
                if ($worker && method_exists($worker, 'isOffDay') && $worker->isOffDay($current->toDateTime())) {
                    $dayData['isOffDay'] = true;
                    $calendar[$week][] = $dayData;
                    $current->addDay();
                    continue;
                }

                // Check if this is a national holiday
                $dateKey = $current->format('Y-m-d');
                if (isset($holidays[$dateKey])) {
                    $dayData['isHoliday'] = true;
                    $dayData['holidayName'] = $holidays[$dateKey]->name;

                    // Jika departemen TIDAK standby, skip shift assignment pada hari libur
                    if (!$requiresHolidayAttendance) {
                        $calendar[$week][] = $dayData;
                        $current->addDay();
                        continue;
                    }
                    // Jika departemen standby, tetap tampilkan shift di bawah
                }

                // Check if there's an override for this date
                $override = $shiftOverrides->first(function ($item) use ($current) {
                    return Carbon::parse($item->override_date)->isSameDay($current);
                });

                if ($override) {
                    $dayData['shift'] = $override->shift;
                    $dayData['isOverride'] = true;
                    if ($override->shift) {
                        $dayData['schedule'] = $override->shift->getScheduleForDate($current->toDateTime());
                    }
                } else {
                    // Find applicable shift from list using model helper
                    $applicableShift = $sortedShifts->first(function ($shift) use ($current) {
                        // For historical shifts (plain objects)
                        if (isset($shift->is_history)) {
                            $dateString = $current->format('Y-m-d');
                            $from = $shift->effective_from ? (is_string($shift->effective_from) ? $shift->effective_from : $shift->effective_from->format('Y-m-d')) : null;
                            $until = $shift->effective_until ? (is_string($shift->effective_until) ? $shift->effective_until : $shift->effective_until->format('Y-m-d')) : null;

                            if ($from && $from > $dateString) return false;
                            if ($until && $until < $dateString) return false;
                            return true;
                        }

                        // For Model objects
                        if (!method_exists($shift, 'isActiveOnDate')) return false;
                        return $shift->isActiveOnDate($current->toDateTime());
                    });

                    if ($applicableShift) {
                        $shiftId = $applicableShift->shift_id;
                        if ($shiftId) {
                            $dayData['shift'] = $applicableShift->shift ?? Shift::find($shiftId);
                            if ($dayData['shift']) {
                                $dayData['schedule'] = $dayData['shift']->getScheduleForDate($current->toDateTime());
                            }
                        }
                    }
                }

                $calendar[$week][] = $dayData;
                $current->addDay();
            }
        }

        return $calendar;
    }
}
