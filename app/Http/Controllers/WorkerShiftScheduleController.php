<?php

namespace App\Http\Controllers;

use App\DTOs\Schedule\WorkerShiftScheduleDTO;
use App\Http\Requests\Schedule\WorkerShiftScheduleRequest;
use App\Services\WorkerShiftScheduleService;
use App\Services\WorkerService;
use App\Services\Master\ShiftService;
use App\Services\Master\ShiftPatternService;
use Illuminate\Http\Request;

class WorkerShiftScheduleController extends Controller
{
    public function __construct(
        private readonly WorkerShiftScheduleService $service,
        private readonly WorkerService $workerService,
        private readonly ShiftService $shiftService,
        private readonly ShiftPatternService $shiftPatternService
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->input('worker_id'),
            'shift_id' => $request->input('shift_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        
        $schedules = $this->service->getAllPaginated(15, $filters);
        $workers = $this->workerService->getActive();
        $shifts = $this->shiftService->getActive();

        return view('admin.workers.schedule', compact(
            'schedules',
            'workers',
            'shifts',
            'filters'
        ));
    }

    public function create()
    {
        $workers = $this->workerService->getActive();
        $shifts = $this->shiftService->getActive();
        $shiftPatterns = $this->shiftPatternService->getActive();
        
        return view('admin.schedules.create', compact(
            'workers',
            'shifts',
            'shiftPatterns'
        ));
    }

    public function store(WorkerShiftScheduleRequest $request)
    {
        $dto = WorkerShiftScheduleDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.schedules.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function show(string $id)
    {
        $schedule = $this->service->findById($id);
        return view('admin.schedules.show', compact('schedule'));
    }

    public function edit(string $id)
    {
        $schedule = $this->service->findById($id);
        $workers = $this->workerService->getActive();
        $shifts = $this->shiftService->getActive();
        $shiftPatterns = $this->shiftPatternService->getActive();
        
        return view('admin.schedules.edit', compact(
            'schedule',
            'workers',
            'shifts',
            'shiftPatterns'
        ));
    }

    public function update(WorkerShiftScheduleRequest $request, string $id)
    {
        $dto = WorkerShiftScheduleDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.schedules.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function destroy(string $id)
    {
        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.schedules.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Generate schedule form
     */
    public function generateForm()
    {
        $workers = $this->workerService->getActive();
        $shifts = $this->shiftService->getActive();
        $shiftPatterns = $this->shiftPatternService->getActive();
        
        return view('admin.schedules.generate', compact(
            'workers',
            'shifts',
            'shiftPatterns'
        ));
    }

    /**
     * Generate schedule
     */
    public function generate(Request $request)
    {
        $request->validate([
            'worker_ids' => 'required|array|min:1',
            'worker_ids.*' => 'exists:workers,id',
            'shift_id' => 'required|exists:shifts,id',
            'shift_pattern_id' => 'required|exists:shift_patterns,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $result = $this->service->generateSchedule(
            $request->worker_ids,
            $request->shift_id,
            $request->shift_pattern_id,
            $request->start_date,
            $request->end_date
        );

        if ($result['success']) {
            return redirect()
                ->route('admin.schedules.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }
}
