<?php

namespace App\Http\Controllers\Schedule;

use App\DTOs\WorkerShiftScheduleDTO;
use App\Http\Requests\Schedule\WorkerShiftScheduleRequest;
use App\Services\Schedule\WorkerShiftScheduleService;
use App\Services\Worker\WorkerService;
use App\Services\Master\ShiftService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkerShiftScheduleController extends Controller
{
    public function __construct(
        private readonly WorkerShiftScheduleService $service,
        private readonly WorkerService $workerService,
        private readonly ShiftService $shiftService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-schedules|view-own-schedule')->only(['index', 'show', 'calendar', 'workerSchedule']);
        $this->middleware('permission:create-schedules')->only(['create', 'store']);
        $this->middleware('permission:edit-schedules')->only(['edit', 'update']);
        $this->middleware('permission:delete-schedules')->only(['destroy']);
        $this->middleware('permission:bulk-create-schedules')->only(['bulkCreate']);
    }

    public function index(Request $request)
    {
        $this->authorizeAnyPermission(['view-schedules', 'view-own-schedule']);

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'shift_id' => $request->input('shift_id'),
            'date' => $request->input('date'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ];

        // Apply permission-based filters
        if (auth()->user()->can('view-own-schedule') && 
            !auth()->user()->can('view-schedules')) {
            $filters['worker_id'] = auth()->user()->worker_id;
        }

        $schedules = $this->service->getAllPaginated(15, $filters);
        
        $workers = auth()->user()->can('view-schedules')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);
            
        $shifts = $this->shiftService->getAll();

        return view('admin.schedules.index', compact('schedules', 'workers', 'shifts', 'filters'));
    }

    public function show(string $id)
    {
        $this->authorizeAnyPermission(['view-schedules', 'view-own-schedule']);

        $schedule = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-schedule') && 
            !auth()->user()->can('view-schedules') &&
            !$this->isOwnData($schedule->worker_id)) {
            abort(403, 'Anda hanya dapat melihat jadwal Anda sendiri.');
        }

        return view('admin.schedules.show', compact('schedule'));
    }

    public function create()
    {
        $this->authorizePermission('create-schedules');

        $workers = $this->workerService->getActive();
        $shifts = $this->shiftService->getAll();

        return view('admin.schedules.create', compact('workers', 'shifts'));
    }

    public function store(WorkerShiftScheduleRequest $request)
    {
        $this->authorizePermission('create-schedules');

        $dto = WorkerShiftScheduleDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.schedules.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $this->authorizePermission('edit-schedules');

        $schedule = $this->service->findById($id);
        $workers = $this->workerService->getActive();
        $shifts = $this->shiftService->getAll();

        return view('admin.schedules.edit', compact('schedule', 'workers', 'shifts'));
    }

    public function update(WorkerShiftScheduleRequest $request, string $id)
    {
        $this->authorizePermission('edit-schedules');

        $dto = WorkerShiftScheduleDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.schedules.show', $id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('delete-schedules');

        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.schedules.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function workerSchedule(Request $request, string $workerId)
    {
        $this->authorizeAnyPermission(['view-schedules', 'view-own-schedule']);

        // Check permission for viewing other worker's schedule
        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('view-schedules');
        }

        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $worker = $this->workerService->findById($workerId);
        $schedules = $this->service->getWorkerMonthlySchedule($workerId, $year, $month);

        return view('admin.schedules.worker-schedule', compact('worker', 'schedules', 'month', 'year'));
    }

    public function bulkCreate(Request $request)
    {
        $this->authorizePermission('bulk-create-schedules');

        $request->validate([
            'worker_ids' => 'required|array',
            'worker_ids.*' => 'exists:workers,id',
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pattern' => 'nullable|string|in:daily,weekly,custom',
        ]);

        $result = $this->service->bulkCreate(
            $request->worker_ids,
            $request->shift_id,
            $request->start_date,
            $request->end_date,
            $request->pattern ?? 'daily'
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function calendar(Request $request)
    {
        $this->authorizeAnyPermission(['view-schedules', 'view-own-schedule']);

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'month' => $request->input('month', date('m')),
            'year' => $request->input('year', date('Y')),
        ];

        // Apply permission-based filters
        if (auth()->user()->can('view-own-schedule') && 
            !auth()->user()->can('view-schedules')) {
            $filters['worker_id'] = auth()->user()->worker_id;
        }

        $workers = auth()->user()->can('view-schedules')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);

        $calendarData = $this->service->getCalendarData($filters);

        return view('admin.schedules.calendar', compact('calendarData', 'workers', 'filters'));
    }
}
