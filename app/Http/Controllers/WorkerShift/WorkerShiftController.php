<?php

namespace App\Http\Controllers\WorkerShift;

use App\Http\Controllers\Controller;
use App\Services\WorkerShift\WorkerShiftService;
use App\Services\Worker\WorkerService;
use App\Services\Master\ShiftService;
use App\Http\Requests\Schedule\WorkerShiftScheduleRequest;
use Illuminate\Http\Request;

class WorkerShiftController extends Controller
{
    public function __construct(
        protected WorkerShiftService $workerShiftService,
        protected WorkerService $workerService,
        protected ShiftService $shiftService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->worker_id,
            'shift_id' => $request->shift_id,
            'is_active' => $request->is_active,
            'per_page' => $request->per_page ?? 15,
        ];

        $workerShifts = $this->workerShiftService->getAll($filters);
        $workers = $this->workerService->getAllActive();
        $shifts = $this->shiftService->getActive();

        return view('admin.schedules.index', compact('workerShifts', 'workers', 'shifts', 'filters'));
    }

    public function create()
    {
        $workers = $this->workerService->getAllActive();
        $shifts = $this->shiftService->getActive();

        return view('admin.schedules.create', compact('workers', 'shifts'));
    }

    public function store(WorkerShiftScheduleRequest $request)
    {
        try {
            $data = $request->validated();

            // Support multiple worker_ids[] or single worker_id
            $workerIds = $data['worker_ids'] ?? null;
            if (empty($workerIds) && !empty($data['worker_id'])) {
                $workerIds = [$data['worker_id']];
            }

            if (empty($workerIds) || !is_array($workerIds)) {
                throw new \Exception('Tidak ada pegawai yang dipilih.');
            }

            $created = 0;
            foreach ($workerIds as $workerId) {
                // Prepare per-worker payload
                $payload = $data;
                $payload['worker_id'] = $workerId;
                // Remove worker_ids to avoid confusion
                unset($payload['worker_ids']);

                $this->workerShiftService->create($payload);
                $created++;
            }

            return redirect()
                ->route('admin.worker-shifts.index')
                ->with('success', "Jadwal shift berhasil ditambahkan untuk {$created} pegawai");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $workerShift = $this->workerShiftService->getById($id);

        return view('admin.schedules.show', compact('workerShift'));
    }

    public function edit(string $id)
    {
        $workerShift = $this->workerShiftService->getById($id);
        $workers = $this->workerService->getAllActive();
        $shifts = $this->shiftService->getActive();

        return view('admin.schedules.edit', compact('workerShift', 'workers', 'shifts'));
    }

    public function update(WorkerShiftScheduleRequest $request, string $id)
    {
        try {
            $this->workerShiftService->update($id, $request->validated());

            return redirect()
                ->route('admin.worker-shifts.show', $id)
                ->with('success', 'Jadwal shift berhasil diperbarui');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->workerShiftService->delete($id);

            return redirect()
                ->route('admin.worker-shifts.index')
                ->with('success', 'Jadwal shift berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function workerShifts(string $workerId)
    {
        $worker = $this->workerService->getById($workerId);
        $workerShifts = $this->workerShiftService->getByWorkerId($workerId);

        return view('admin.schedules.worker-shifts', compact('worker', 'workerShifts'));
    }
}
