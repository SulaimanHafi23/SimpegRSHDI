<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Requests\Schedule\WorkerShiftScheduleRequest;
use App\Models\Shift;
use App\Models\Worker;
use App\Models\WorkerShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class WorkerShiftScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:schedule.manage|view-own-schedule')->only(['index', 'show', 'calendar', 'workerSchedule']);
        $this->middleware('permission:schedule.manage')->only(['create', 'store']);
        $this->middleware('permission:schedule.manage')->only(['edit', 'update']);
        $this->middleware('permission:schedule.manage')->only(['destroy']);
        $this->middleware('permission:schedule.manage')->only(['bulkCreate']);
    }

    public function index(Request $request)
    {
        $this->authorizeAnyPermission(['view-schedules', 'view-own-schedule']);

        $canViewAll = Gate::allows('view-schedules');
        $canViewOwn = Gate::allows('view-own-schedule');

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'shift_id' => $request->input('shift_id'),
            'date' => $request->input('date'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'per_page' => 15,
        ];

        // Apply permission-based filters
        $user = Auth::user();
        if ($canViewOwn && !$canViewAll) {
            $filters['worker_id'] = $user?->worker_id;
        }

        $scheduleQuery = WorkerShift::with(['worker.department', 'shift'])
            ->when(!empty($filters['worker_id']), fn($q) => $q->where('worker_id', $filters['worker_id']))
            ->when(!empty($filters['shift_id']), fn($q) => $q->where('shift_id', $filters['shift_id']))
            ->when($filters['date'] ?? null, function ($q) use ($filters) {
                $date = $filters['date'];
                $q->whereDate('effective_from', '<=', $date)
                    ->where(function ($q2) use ($date) {
                        $q2->whereNull('effective_until')
                            ->orWhereDate('effective_until', '>=', $date);
                    });
            })
            ->when($filters['start_date'] ?? null, fn($q) => $q->whereDate('effective_from', '>=', $filters['start_date']))
            ->when($filters['end_date'] ?? null, fn($q) => $q->whereDate('effective_from', '<=', $filters['end_date']))
            ->when(!empty($filters['month']) && !empty($filters['year']), function ($q) use ($filters) {
                $month = str_pad((string) $filters['month'], 2, '0', STR_PAD_LEFT);
                $start = Carbon::createFromDate((int) $filters['year'], (int) $month, 1)->startOfMonth()->toDateString();
                $end = Carbon::createFromDate((int) $filters['year'], (int) $month, 1)->endOfMonth()->toDateString();

                $q->where(function ($q2) use ($start, $end) {
                    $q2->whereBetween('effective_from', [$start, $end])
                        ->orWhereBetween('effective_until', [$start, $end])
                        ->orWhere(function ($q3) use ($start, $end) {
                            $q3->whereDate('effective_from', '<=', $start)
                                ->where(function ($q4) use ($end) {
                                    $q4->whereNull('effective_until')
                                        ->orWhereDate('effective_until', '>=', $end);
                                });
                        });
                });
            })
            ->when($filters['is_active'] !== null && $filters['is_active'] !== '', fn($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->orderByDesc('effective_from');

        $schedules = $scheduleQuery->paginate((int) $filters['per_page'])->appends($filters);

        $workers = $canViewAll
            ? Worker::where('status', 'active')->with(['department'])->orderBy('name')->get()
            : collect([$user?->worker])->filter();

        $shifts = Shift::orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'workers', 'shifts', 'filters'));
    }

    public function show(string $id)
    {
        $this->authorizeAnyPermission(['view-schedules', 'view-own-schedule']);

        $schedule = WorkerShift::with(['worker.department', 'shift'])->findOrFail($id);

        // Check own data permission
        if (Gate::allows('view-own-schedule') &&
            !Gate::allows('view-schedules') &&
            !$this->isOwnData($schedule->worker_id)) {
            abort(403, 'Anda hanya dapat melihat jadwal Anda sendiri.');
        }

        return view('admin.schedules.show', compact('schedule'));
    }

    public function create()
    {
        $this->authorizePermission('schedule.manage');

        $workers = Worker::where('status', 'active')->with(['department'])->orderBy('name')->get();
        $shifts = Shift::orderBy('name')->get();

        return view('admin.schedules.create', compact('workers', 'shifts'));
    }

    public function store(WorkerShiftScheduleRequest $request)
    {
        $this->authorizePermission('schedule.manage');

        try {
            $data = $request->validated();
            $workerIds = $data['worker_ids'] ?? [];
            if (empty($workerIds) && !empty($data['worker_id'])) {
                $workerIds = [$data['worker_id']];
            }

            $firstCreatedId = null;
            foreach ($workerIds as $workerId) {
                $created = WorkerShift::create([
                    'worker_id' => $workerId,
                    'shift_id' => $data['shift_id'],
                    'effective_from' => $data['start_date'],
                    'effective_until' => $data['end_date'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                ]);
                $firstCreatedId ??= $created->id;
            }

            return redirect()
                ->route('admin.schedules.show', $firstCreatedId)
                ->with('success', 'Jadwal shift berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(string $id)
    {
        $this->authorizePermission('schedule.manage');

        $schedule = WorkerShift::with(['worker.department', 'shift'])->findOrFail($id);
        $workers = Worker::where('status', 'active')->with(['department'])->orderBy('name')->get();
        $shifts = Shift::orderBy('name')->get();

        return view('admin.schedules.edit', compact('schedule', 'workers', 'shifts'));
    }

    public function update(WorkerShiftScheduleRequest $request, string $id)
    {
        $this->authorizePermission('schedule.manage');

        try {
            $data = $request->validated();
            $schedule = WorkerShift::findOrFail($id);
            $schedule->update([
                'worker_id' => $data['worker_id'] ?? null,
                'shift_id' => $data['shift_id'],
                'effective_from' => $data['start_date'],
                'effective_until' => $data['end_date'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return redirect()
                ->route('admin.schedules.show', $id)
                ->with('success', 'Jadwal shift berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('schedule.manage');

        try {
            $schedule = WorkerShift::findOrFail($id);
            $schedule->delete();
            return redirect()
                ->route('admin.schedules.index')
                ->with('success', 'Jadwal shift berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function workerSchedule(Request $request, string $workerId)
    {
        $this->authorizeAnyPermission(['view-schedules', 'view-own-schedule']);

        // Check permission for viewing other worker's schedule
        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('schedule.manage');
        }

        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $worker = Worker::with(['department', 'user'])->findOrFail($workerId);
        $startDate = Carbon::createFromDate((int) $year, (int) $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $schedules = WorkerShift::with(['shift'])
            ->where('worker_id', $workerId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('effective_from', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('effective_until', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('effective_from', '<=', $startDate->toDateString())
                            ->where(function ($q3) use ($endDate) {
                                $q3->whereNull('effective_until')
                                    ->orWhere('effective_until', '>=', $endDate->toDateString());
                            });
                    });
            })
            ->orderBy('effective_from')
            ->get();

        return view('admin.schedules.worker-schedule', compact('worker', 'schedules', 'month', 'year'));
    }

    public function bulkCreate(Request $request)
    {
        $this->authorizePermission('schedule.manage');

        $request->validate([
            'worker_ids' => 'required|array',
            'worker_ids.*' => 'exists:workers,id',
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pattern' => 'nullable|string|in:daily,weekly,custom',
        ]);

        try {
            $workerIds = $request->worker_ids;
            $shiftId = $request->shift_id;
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->startOfDay();
            $pattern = $request->pattern ?? 'daily';

            $created = 0;
            foreach ($workerIds as $workerId) {
                if ($pattern === 'daily') {
                    $cursor = $startDate->copy();
                    while ($cursor->lte($endDate)) {
                        WorkerShift::create([
                            'worker_id' => $workerId,
                            'shift_id' => $shiftId,
                            'effective_from' => $cursor->toDateString(),
                            'effective_until' => $cursor->toDateString(),
                            'is_active' => true,
                        ]);
                        $created++;
                        $cursor->addDay();
                    }
                } elseif ($pattern === 'weekly') {
                    $cursor = $startDate->copy();
                    while ($cursor->lte($endDate)) {
                        $periodEnd = $cursor->copy()->addDays(6);
                        if ($periodEnd->gt($endDate)) {
                            $periodEnd = $endDate->copy();
                        }

                        WorkerShift::create([
                            'worker_id' => $workerId,
                            'shift_id' => $shiftId,
                            'effective_from' => $cursor->toDateString(),
                            'effective_until' => $periodEnd->toDateString(),
                            'is_active' => true,
                        ]);
                        $created++;
                        $cursor = $periodEnd->copy()->addDay();
                    }
                } else {
                    WorkerShift::create([
                        'worker_id' => $workerId,
                        'shift_id' => $shiftId,
                        'effective_from' => $startDate->toDateString(),
                        'effective_until' => $endDate->toDateString(),
                        'is_active' => true,
                    ]);
                    $created++;
                }
            }

            return back()->with('success', "Bulk create jadwal berhasil: {$created} jadwal.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function calendar(Request $request)
    {
        $this->authorizeAnyPermission(['view-schedules', 'view-own-schedule']);

        $canViewAll = Gate::allows('view-schedules');
        $canViewOwn = Gate::allows('view-own-schedule');

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'month' => $request->input('month', date('m')),
            'year' => $request->input('year', date('Y')),
        ];

        // Apply permission-based filters
        $user = Auth::user();
        if ($canViewOwn && !$canViewAll) {
            $filters['worker_id'] = $user?->worker_id;
        }

        $workers = $canViewAll
            ? Worker::where('status', 'active')->with(['department'])->orderBy('name')->get()
            : collect([$user?->worker])->filter();

        $calendarData = $this->buildCalendarData($filters);

        return view('admin.schedules.calendar', compact('calendarData', 'workers', 'filters'));
    }

    private function buildCalendarData(array $filters): array
    {
        $startDate = Carbon::createFromDate((int) $filters['year'], (int) $filters['month'], 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $schedules = WorkerShift::with(['worker.department', 'shift'])
            ->when(!empty($filters['worker_id']), fn($q) => $q->where('worker_id', $filters['worker_id']))
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('effective_from', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('effective_until', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('effective_from', '<=', $startDate->toDateString())
                            ->where(function ($q3) use ($endDate) {
                                $q3->whereNull('effective_until')
                                    ->orWhere('effective_until', '>=', $endDate->toDateString());
                            });
                    });
            })
            ->orderBy('effective_from')
            ->get();

        return [
            'month' => $filters['month'],
            'year' => $filters['year'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'schedules' => $schedules,
        ];
    }
}
