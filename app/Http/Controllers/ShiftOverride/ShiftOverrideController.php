<?php

namespace App\Http\Controllers\ShiftOverride;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftOverride;
use App\Models\Worker;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftOverrideController extends Controller
{
    use DepartmentFilterable;

    public function __construct() {}

    public function index(Request $request)
    {
        $departmentId = $this->getManagerDepartmentFilter();

        $filters = [
            'worker_id' => $request->worker_id,
            'override_date' => $request->override_date,
            'department_id' => $departmentId,
            'per_page' => $request->per_page ?? 15,
        ];

        $query = ShiftOverride::with(['worker', 'shift', 'creator']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('worker', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        $shiftOverrides = $query->latest('override_date')
            ->paginate($filters['per_page'])
            ->appends($filters);

        $workers = Worker::where('status', 'active')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->with(['department'])
            ->get();

        return view('admin.shift-overrides.index', compact('shiftOverrides', 'workers'));
    }

    public function create()
    {
        $departmentId = $this->getManagerDepartmentFilter();
        $workers = Worker::where('status', 'active')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->with(['department'])
            ->get();
        $shifts = Shift::where('is_active', true)->orderBy('name')->get();

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
            $existing = ShiftOverride::where('worker_id', $validated['worker_id'])
                ->where('override_date', $validated['override_date'])
                ->first();

            if ($existing) {
                throw new \Exception('Shift override already exists for this date.');
            }

            ShiftOverride::create($validated);

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
            foreach ($validated['dates'] as $date) {
                try {
                    $existing = ShiftOverride::where('worker_id', $validated['worker_id'])
                        ->where('override_date', $date)
                        ->first();

                    if ($existing) {
                        continue;
                    }

                    ShiftOverride::create([
                        'worker_id' => $validated['worker_id'],
                        'shift_id' => $validated['shift_id'],
                        'override_date' => $date,
                        'reason' => $validated['reason'] ?? null,
                        'created_by' => Auth::id(),
                    ]);
                } catch (\Exception $e) {
                    continue;
                }
            }

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
            ShiftOverride::findOrFail($id)->delete();

            return redirect()
                ->route('admin.shift-overrides.index')
                ->with('success', 'Override shift berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
