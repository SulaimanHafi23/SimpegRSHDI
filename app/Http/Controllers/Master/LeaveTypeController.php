<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\LeaveTypeService;
use App\DTOs\Master\LeaveTypeDTO;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct(
        protected LeaveTypeService $leaveTypeService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:leave-type.view')->only(['index', 'show']);
        $this->middleware('permission:leave-type.create')->only(['create', 'store']);
        $this->middleware('permission:leave-type.edit')->only(['edit', 'update']);
        $this->middleware('permission:leave-type.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        
        if ($request->has('search')) {
            $leaveTypes = $this->leaveTypeService->search($request->search, $perPage);
        } else {
            $leaveTypes = $this->leaveTypeService->getAllPaginated($perPage);
        }

        return view('admin.master.leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('admin.master.leave-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'code' => 'required|string|max:50|unique:leave_types,code',
            'description' => 'nullable|string',
            'max_days_per_year' => 'required|integer|min:1',
            'is_paid' => 'nullable|boolean',
            'requires_approval' => 'nullable|boolean',
            'requires_attachment' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = LeaveTypeDTO::fromRequest($validated);
            $result = $this->leaveTypeService->create($dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.leave-types.index')
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
            $leaveType = $this->leaveTypeService->findById($id);
            return view('admin.master.leave-types.show', compact('leaveType'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.leave-types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $leaveType = $this->leaveTypeService->findById($id);
            return view('admin.master.leave-types.edit', compact('leaveType'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.leave-types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name,' . $id,
            'code' => 'required|string|max:50|unique:leave_types,code,' . $id,
            'description' => 'nullable|string',
            'max_days_per_year' => 'required|integer|min:1',
            'is_paid' => 'nullable|boolean',
            'requires_approval' => 'nullable|boolean',
            'requires_attachment' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = LeaveTypeDTO::fromRequest($validated);
            $result = $this->leaveTypeService->update($id, $dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.leave-types.show', $id)
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
            $result = $this->leaveTypeService->delete($id);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.leave-types.index')
                    ->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
