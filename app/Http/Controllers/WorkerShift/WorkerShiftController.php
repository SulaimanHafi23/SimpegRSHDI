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

    public function store(Request $request)
    {
        // Validasi manual untuk menghindari konflik 'required_without' 
        // yang menyebabkan error "Pegawai field is required when Pegawai is not present"
        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'worker_id' => 'nullable|exists:workers,id',
            'worker_ids' => 'nullable|array',
            'worker_ids.*' => 'exists:workers,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'pattern_type' => 'nullable|string|in:fixed,rotating,custom',
            'custom_working_days' => 'nullable|array',
            'rotating_days' => 'nullable|array',
        ], [
            'shift_id.required' => 'Shift wajib dipilih.',
            'shift_id.exists' => 'Shift yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'worker_id.exists' => 'Pegawai yang dipilih tidak valid.',
            'worker_ids.array' => 'Format data pegawai tidak valid.',
            'worker_ids.*.exists' => 'Salah satu pegawai yang dipilih tidak valid.',
            'pattern_type.in' => 'Tipe pola harus berisi fixed, rotating, atau custom.',
        ]);

        if (empty($data['worker_id']) && empty($data['worker_ids'])) {
            return back()->withInput()->withErrors(['worker_id' => 'Harap pilih minimal satu pegawai.']);
        }

        try {

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
                $payload['pattern_type'] = $data['pattern_type'] ?? 'fixed';
                $payload['is_active'] = $data['is_active'] ?? true;
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

    public function update(Request $request, string $id)
    {
        // Validasi manual untuk update (bypass WorkerShiftScheduleRequest)
        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'pattern_type' => 'nullable|string|in:fixed,rotating,custom',
            'custom_working_days' => 'nullable|array',
            'rotating_days' => 'nullable|array',
        ], [
            'shift_id.required' => 'Shift wajib dipilih.',
            'shift_id.exists' => 'Shift yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'pattern_type.in' => 'Tipe pola harus berisi fixed, rotating, atau custom.',
        ]);

        try {
            // Pastikan pattern_type dan is_active terisi
            $data['pattern_type'] = $data['pattern_type'] ?? 'fixed';
            $data['is_active'] = $request->boolean('is_active'); // Mengambil nilai checkbox dengan benar

            $this->workerShiftService->update($id, $data);

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
