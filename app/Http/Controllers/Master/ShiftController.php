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
                'day_times' => 'nullable|array',
                'day_times.*.start_time' => 'nullable|date_format:H:i',
                'day_times.*.end_time' => 'nullable|date_format:H:i',
                'day_active' => 'nullable|array',
                'per_day_enabled' => 'nullable|boolean',
            ]);

            // Convert checkbox value
            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['is_overnight'] = $request->has('is_overnight') ? true : false;

            $dto = ShiftDTO::fromRequest($validated);
            $result = $this->shiftService->create($dto);

            if ($result['success']) {
                $perDayEnabled = $request->boolean('per_day_enabled');
                if ($perDayEnabled) {
                    $this->syncDayTimes(
                        $result['data'],
                        $validated['day_times'] ?? [],
                        array_keys($validated['day_active'] ?? [])
                    );
                } else {
                    $this->syncDayTimes($result['data'], [], []);
                }
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
            'day_times' => 'nullable|array',
            'day_times.*.start_time' => 'nullable|date_format:H:i',
            'day_times.*.end_time' => 'nullable|date_format:H:i',
            'day_active' => 'nullable|array',
            'per_day_enabled' => 'nullable|boolean',
        ]);

        try {
            $dto = ShiftDTO::fromRequest($validated);
            $result = $this->shiftService->update($id, $dto);

            if ($result['success']) {
                $perDayEnabled = $request->boolean('per_day_enabled');
                if ($perDayEnabled) {
                    $this->syncDayTimes(
                        $result['data'],
                        $validated['day_times'] ?? [],
                        array_keys($validated['day_active'] ?? [])
                    );
                } else {
                    $this->syncDayTimes($result['data'], [], []);
                }
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

    private function syncDayTimes($shift, array $dayTimes, array $activeDays): void
    {
        $cleaned = [];

        $activeDays = array_map('intval', $activeDays);

        if (empty($activeDays)) {
            $shift->dayTimes()->delete();
            return;
        }

        foreach ($dayTimes as $day => $times) {
            if (!in_array((int) $day, $activeDays, true)) {
                continue;
            }
            $start = $times['start_time'] ?? null;
            $end = $times['end_time'] ?? null;

            if ($start === null || $end === null) {
                throw new \InvalidArgumentException('Jam masuk dan jam keluar per hari harus diisi lengkap.');
            }

            $cleaned[(int) $day] = [
                'start_time' => $start,
                'end_time' => $end,
            ];
        }

        if (empty($cleaned)) {
            $shift->dayTimes()->delete();
            return;
        }

        $shift->dayTimes()->whereNotIn('day_of_week', array_keys($cleaned))->delete();

        foreach ($cleaned as $day => $times) {
            $shift->dayTimes()->updateOrCreate(
                ['day_of_week' => $day],
                ['start_time' => $times['start_time'], 'end_time' => $times['end_time']]
            );
        }
    }
}
