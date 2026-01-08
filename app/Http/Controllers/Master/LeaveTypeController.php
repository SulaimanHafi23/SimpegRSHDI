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
        $this->middleware('permission:leave-type.manage')->only(['index', 'show']);
        $this->middleware('permission:leave-type.manage')->only(['create', 'store']);
        $this->middleware('permission:leave-type.manage')->only(['edit', 'update']);
        $this->middleware('permission:leave-type.manage')->only('destroy');
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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:leave_types,name',
                'code' => 'required|string|max:50|unique:leave_types,code',
                'description' => 'nullable|string',
                'max_days_per_year' => 'required|integer|min:1',
                'days_notice' => 'required|integer|min:0',
                'is_paid' => 'nullable|boolean',
                'requires_approval' => 'nullable|boolean',
                'requires_attachment' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            // Convert checkbox values
            $validated['is_paid'] = $request->has('is_paid') ? true : false;
            $validated['requires_approval'] = $request->has('requires_approval') ? true : false;
            $validated['requires_attachment'] = $request->has('requires_attachment') ? true : false;
            $validated['is_active'] = $request->has('is_active') ? true : false;

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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Validasi gagal. Periksa kembali input Anda.');
        } catch (\Exception $e) {
            \Log::error('Error creating leave type: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:leave_types,name,' . $id,
                'code' => 'required|string|max:50|unique:leave_types,code,' . $id,
                'description' => 'nullable|string',
                'max_days_per_year' => 'required|integer|min:1',
                'days_notice' => 'required|integer|min:0',
                'is_paid' => 'nullable|boolean',
                'requires_approval' => 'nullable|boolean',
                'requires_attachment' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            // Convert checkbox values
            $validated['is_paid'] = $request->has('is_paid') ? true : false;
            $validated['requires_approval'] = $request->has('requires_approval') ? true : false;
            $validated['requires_attachment'] = $request->has('requires_attachment') ? true : false;
            $validated['is_active'] = $request->has('is_active') ? true : false;

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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Validasi gagal. Periksa kembali input Anda.');
        } catch (\Exception $e) {
            \Log::error('Error updating leave type: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
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
