<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\WorkerShift\WorkerShiftService;
use App\Services\ShiftOverride\ShiftOverrideService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function __construct(
        protected WorkerShiftService $workerShiftService,
        protected ShiftOverrideService $shiftOverrideService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display employee's shift schedule
     */
    public function index(Request $request)
    {
        $user = auth()->user();
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

        // Get all worker shifts to handle date ranges correctly
        $workerShifts = $this->workerShiftService->getAll([
            'worker_id' => $worker->id,
            'per_page' => 1000, // Ensure we get enough records
        ]);

        if (method_exists($workerShifts, 'items')) {
            $workerShifts = $workerShifts->items();
        }

        // Get shift overrides for this month
        $shiftOverrides = $this->shiftOverrideService->getAll([
            'worker_id' => $worker->id,
            'date_from' => $startOfMonth->format('Y-m-d'),
            'date_to' => $endOfMonth->format('Y-m-d'),
        ]);

        // convert paginator to collection for easier lookup
        if (method_exists($shiftOverrides, 'items')) {
            $shiftOverrides = collect($shiftOverrides->items());
        }

        // Build calendar data
        $requiresHolidayAttendance = $worker->department && $worker->department->requires_holiday_attendance;
        $calendar = $this->buildCalendar($worker, $startOfMonth, $endOfMonth, $workerShifts, $shiftOverrides, $holidays, $requiresHolidayAttendance);

        // Urutkan shift dari yang terbaru (effective_from descending) untuk penentuan info header
        $sortedShifts = collect($workerShifts)->sortByDesc('effective_from');

        // Determine current active shift using service helper (more reliable)
        $workerShift = $this->workerShiftService->getActiveByWorkerId($worker->id);

        // FALLBACK: Jika tidak ada jadwal aktif saat ini, ambil jadwal terakhir (terbaru)
        if (!$workerShift && count($workerShifts) > 0) {
            $workerShift = $sortedShifts->first();
        }

        // Get shift history for this worker
        $shiftHistories = $this->workerShiftService->getShiftHistories($worker->id);

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
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $date = Carbon::parse($request->date);

        // Get worker shift for specific date
        $workerShifts = $this->workerShiftService->getAll([
            'worker_id' => $worker->id,
            'per_page' => 100,
        ]);

        if (method_exists($workerShifts, 'items')) {
            $workerShifts = $workerShifts->items();
        }

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
        $override = $this->shiftOverrideService->getAll([
            'worker_id' => $worker->id,
            'date_from' => $date->format('Y-m-d'),
            'date_to' => $date->format('Y-m-d'),
        ])->first();

        return view('employee.shifts.show', compact(
            'date',
            'workerShift',
            'override'
        ));
    }

    /**
     * Build calendar array with shift information
     */
    private function buildCalendar($worker, $startOfMonth, $endOfMonth, $workerShifts, $shiftOverrides, $holidays, $requiresHolidayAttendance = false)
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
                    'isHoliday' => false,
                    'holidayName' => null,
                ];

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
                        if (!method_exists($shift, 'isActiveOnDate')) return false;
                        return $shift->isActiveOnDate($current->toDateTime());
                    });

                    if ($applicableShift) {
                        $shiftId = $applicableShift->getShiftForDate($current->toDateTime());
                        if ($shiftId) {
                            $dayData['shift'] = \App\Models\Shift::find($shiftId);
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
