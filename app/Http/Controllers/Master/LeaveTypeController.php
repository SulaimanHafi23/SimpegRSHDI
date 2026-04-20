<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:leave-type.manage')->only(['index', 'show']);
        $this->middleware('permission:leave-type.manage')->only(['create', 'store']);
        $this->middleware('permission:leave-type.manage')->only(['edit', 'update']);
        $this->middleware('permission:leave-type.manage')->only('destroy');
    }

    /**
     * Display list of leave types
     */
    public function index(Request $request)
    {
        $query = LeaveType::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $leaveTypes = $query->latest()->paginate($request->per_page ?? 15);

        return view('admin.master.leave-types.index', compact('leaveTypes'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.master.leave-types.create');
    }

    /**
     * Store new leave type
     */
    public function store(Request $request)
    {
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

        try {
            DB::beginTransaction();

            // Convert checkbox values
            $validated['is_paid'] = $request->has('is_paid');
            $validated['requires_approval'] = $request->has('requires_approval');
            $validated['requires_attachment'] = $request->has('requires_attachment');
            $validated['is_active'] = $request->has('is_active');

            LeaveType::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.master.leave-types.index')
                ->with('success', 'Tipe cuti berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating leave type: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show leave type details
     */
    public function show(string $id)
    {
        try {
            $leaveType = LeaveType::findOrFail($id);
            return view('admin.master.leave-types.show', compact('leaveType'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.leave-types.index')
                ->with('error', 'Tipe cuti tidak ditemukan');
        }
    }

    /**
     * Show edit form
     */
    public function edit(string $id)
    {
        try {
            $leaveType = LeaveType::findOrFail($id);
            return view('admin.master.leave-types.edit', compact('leaveType'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.leave-types.index')
                ->with('error', 'Tipe cuti tidak ditemukan');
        }
    }

    /**
     * Update leave type
     */
    public function update(Request $request, string $id)
    {
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

        try {
            DB::beginTransaction();

            $leaveType = LeaveType::findOrFail($id);

            // Convert checkbox values
            $validated['is_paid'] = $request->has('is_paid');
            $validated['requires_approval'] = $request->has('requires_approval');
            $validated['requires_attachment'] = $request->has('requires_attachment');
            $validated['is_active'] = $request->has('is_active');

            $leaveType->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.master.leave-types.show', $id)
                ->with('success', 'Tipe cuti berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating leave type: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete leave type
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $leaveType = LeaveType::findOrFail($id);

            // Check if leave type is used
            if ($leaveType->leaveRequests()->exists()) {
                throw new \Exception('Tidak dapat menghapus tipe cuti yang masih digunakan');
            }

            $leaveType->delete();

            DB::commit();

            return redirect()
                ->route('admin.master.leave-types.index')
                ->with('success', 'Tipe cuti berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting leave type: ' . $e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }
}
