<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\DepartmentService;
use App\DTOs\Master\DepartmentDTO;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:department.manage')->only(['index', 'show']);
        $this->middleware('permission:department.manage')->only(['create', 'store']);
        $this->middleware('permission:department.manage')->only(['edit', 'update']);
        $this->middleware('permission:department.manage')->only('destroy');
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'is_active' => $request->is_active,
            'per_page' => $request->per_page ?? 15,
        ];

        $departments = $this->departmentService->getAll($filters);

        return view('admin.master.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.master.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = DepartmentDTO::fromRequest($validated);
            $result = $this->departmentService->create($dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.departments.index')
                    ->with('success', $result['message']);
            }

            return back()
                ->withInput()
                ->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $department = $this->departmentService->findById($id);
            $statistics = [
                'total_workers' => $department->workers()->count(),
                'active_workers' => $department->workers()->where('status', 'active')->count(),
            ];

            return view('admin.master.departments.show', compact('department', 'statistics'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.departments.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $department = $this->departmentService->findById($id);
            $allDepartments = $this->departmentService->getAll(['is_active' => true]);
            
            return view('admin.master.departments.edit', compact('department', 'allDepartments'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.departments.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'code' => 'required|string|max:50|unique:departments,code,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = DepartmentDTO::fromRequest($validated);
            $result = $this->departmentService->update($id, $dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.departments.show', $id)
                    ->with('success', $result['message']);
            }

            return back()
                ->withInput()
                ->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $result = $this->departmentService->delete($id);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.departments.index')
                    ->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
