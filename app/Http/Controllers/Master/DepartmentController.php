<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:department.manage')->only(['index', 'show']);
        $this->middleware('permission:department.manage')->only(['create', 'store']);
        $this->middleware('permission:department.manage')->only(['edit', 'update']);
        $this->middleware('permission:department.manage')->only('destroy');
    }

    /**
     * Display list of departments
     */
    public function index(Request $request)
    {
        $query = Department::query()->withCount('workers');

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        $departments = $query->latest()->paginate($request->per_page ?? 15);

        return view('admin.master.departments.index', compact('departments'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.master.departments.create');
    }

    /**
     * Store new department
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'requires_holiday_attendance' => 'nullable|boolean',
            'parent_id' => 'nullable|uuid|exists:departments,id',
            'manager_id' => 'nullable|uuid|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            // Check if name already exists
            if (Department::where('name', $validated['name'])->exists()) {
                throw new \Exception('Nama department sudah digunakan');
            }

            // Check if code already exists
            if (Department::where('code', $validated['code'])->exists()) {
                throw new \Exception('Kode department sudah digunakan');
            }

            // Check if parent exists
            if (!empty($validated['parent_id']) && !Department::find($validated['parent_id'])) {
                throw new \Exception('Parent department tidak ditemukan');
            }

            // Convert boolean values
            $validated['is_active'] = $request->has('is_active');
            $validated['requires_holiday_attendance'] = $request->has('requires_holiday_attendance');

            $department = Department::create($validated);

            DB::commit();
            Cache::forget('master_departments_active');

            return redirect()
                ->route('admin.master.departments.index')
                ->with('success', 'Department berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating department: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show department details
     */
    public function show(string $id)
    {
        try {
            $department = Department::with('workers')->findOrFail($id);
            $statistics = [
                'total_workers' => $department->workers()->count(),
                'active_workers' => $department->workers()->where('status', 'active')->count(),
            ];

            return view('admin.master.departments.show', compact('department', 'statistics'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.departments.index')
                ->with('error', 'Department tidak ditemukan');
        }
    }

    /**
     * Show edit form
     */
    public function edit(string $id)
    {
        try {
            $department = Department::findOrFail($id);
            // Get all active departments for parent selection
            $allDepartments = Department::where('is_active', true)
                ->where('id', '!=', $id)
                ->orderBy('name')
                ->get();

            return view('admin.master.departments.edit', compact('department', 'allDepartments'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.departments.index')
                ->with('error', 'Department tidak ditemukan');
        }
    }

    /**
     * Update department
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'code' => 'required|string|max:50|unique:departments,code,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'requires_holiday_attendance' => 'nullable|boolean',
            'parent_id' => 'nullable|uuid|exists:departments,id',
            'manager_id' => 'nullable|uuid|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $department = Department::findOrFail($id);

            // Check if name already exists (except current)
            $existingByName = Department::where('name', $validated['name'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingByName) {
                throw new \Exception('Nama department sudah digunakan');
            }

            // Check if code already exists (except current)
            $existingByCode = Department::where('code', $validated['code'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingByCode) {
                throw new \Exception('Kode department sudah digunakan');
            }

            // Check if parent exists
            if (!empty($validated['parent_id']) && !Department::find($validated['parent_id'])) {
                throw new \Exception('Parent department tidak ditemukan');
            }

            // Prevent setting itself as parent
            if ($validated['parent_id'] === $id) {
                throw new \Exception('Department tidak bisa menjadi parent dari dirinya sendiri');
            }

            // Convert boolean values
            $validated['is_active'] = $request->has('is_active');
            $validated['requires_holiday_attendance'] = $request->has('requires_holiday_attendance');

            $department->update($validated);

            DB::commit();
            Cache::forget('master_departments_active');

            return redirect()
                ->route('admin.master.departments.show', $id)
                ->with('success', 'Department berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating department: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete department
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $department = Department::findOrFail($id);

            // Check if department has workers
            if ($department->workers()->exists()) {
                throw new \Exception('Tidak dapat menghapus department yang masih memiliki worker');
            }

            $department->delete();

            DB::commit();
            Cache::forget('master_departments_active');

            return redirect()
                ->route('admin.master.departments.index')
                ->with('success', 'Department berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting department: ' . $e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }
}
