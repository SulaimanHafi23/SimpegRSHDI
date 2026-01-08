<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\ShiftService;
use App\DTOs\Master\ShiftDTO;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(
        protected ShiftService $shiftService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:shift.manage');
    }

    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        
        if ($request->has('search')) {
            $shifts = $this->shiftService->search($request->search, $perPage);
        } else {
            $shifts = $this->shiftService->getAllPaginated($perPage);
        }

        return view('admin.master.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.master.shifts.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:shifts,name',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'total_hours' => 'required|numeric|min:0.01|max:24',
                'grace_period_minutes' => 'nullable|integer|min:0|max:60',
                'is_overnight' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            // Convert checkbox value
            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['is_overnight'] = $request->has('is_overnight') ? true : false;

            $dto = ShiftDTO::fromRequest($validated);
            $result = $this->shiftService->create($dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.shifts.index')
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
            \Log::error('Error creating shift: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $shift = $this->shiftService->findById($id);
            $statistics = $this->shiftService->getShiftStatistics($id);

            return view('admin.master.shifts.show', compact('shift', 'statistics'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.shifts.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $shift = $this->shiftService->findById($id);
            return view('admin.master.shifts.edit', compact('shift'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.shifts.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shifts,name,' . $id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'total_hours' => 'required|numeric|min:0.01|max:24',
            'grace_period_minutes' => 'nullable|integer|min:0|max:60',
            'is_overnight' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = ShiftDTO::fromRequest($validated);
            $result = $this->shiftService->update($id, $dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.shifts.show', $id)
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
            $result = $this->shiftService->delete($id);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.shifts.index')
                    ->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
