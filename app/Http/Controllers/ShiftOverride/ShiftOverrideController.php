<?php

namespace App\Http\Controllers\ShiftOverride;

use App\Http\Controllers\Controller;
use App\Services\ShiftOverride\ShiftOverrideService;
use App\Services\Worker\WorkerService;
use App\Services\Master\ShiftService;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftOverrideController extends Controller
{
    use DepartmentFilterable;

    public function __construct(
        protected ShiftOverrideService $shiftOverrideService,
        protected WorkerService $workerService,
        protected ShiftService $shiftService
    ) {}

    public function index(Request $request)
    {
        $departmentId = $this->getManagerDepartmentFilter();

        $filters = [
            'worker_id' => $request->worker_id,
            'override_date' => $request->override_date,
            'department_id' => $departmentId,
            'per_page' => $request->per_page ?? 15,
        ];

        $shiftOverrides = $this->shiftOverrideService->getAll($filters);
        $workers = $departmentId
            ? $this->workerService->getByDepartment($departmentId)
            : $this->workerService->getAllActive();

        return view('admin.shift-overrides.index', compact('shiftOverrides', 'workers'));
    }

    public function create()
    {
        $departmentId = $this->getManagerDepartmentFilter();
        $workers = $departmentId
            ? $this->workerService->getByDepartment($departmentId)
            : $this->workerService->getAllActive();
        $shifts = $this->shiftService->getActive();

        return view('admin.shift-overrides.create', compact('workers', 'shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|uuid|exists:workers,id',
            'shift_id' => 'required|uuid|exists:shifts,id',
            'override_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        try {
            $this->shiftOverrideService->create($validated);

            return redirect()
                ->route('admin.shift-overrides.index')
                ->with('success', 'Override shift berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|uuid|exists:workers,id',
            'shift_id' => 'required|uuid|exists:shifts,id',
            'dates' => 'required|array',
            'dates.*' => 'date',
            'reason' => 'nullable|string',
        ]);

        try {
            $this->shiftOverrideService->bulkCreate(
                $validated['worker_id'],
                $validated['shift_id'],
                $validated['dates'],
                Auth::id(),
                $validated['reason'] ?? null
            );

            return redirect()
                ->route('admin.shift-overrides.index')
                ->with('success', 'Bulk override shift berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->shiftOverrideService->delete($id);

            return redirect()
                ->route('admin.shift-overrides.index')
                ->with('success', 'Override shift berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
