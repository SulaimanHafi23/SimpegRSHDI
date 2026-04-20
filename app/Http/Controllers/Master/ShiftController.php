<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:shift.manage');
    }

    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;

        $query = Shift::withCount('workerShifts');
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $shifts = $query->latest()->paginate($perPage);

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

            DB::beginTransaction();

            if (!$this->isValidTimeRange($validated['start_time'], $validated['end_time'])) {
                throw new \Exception('Jam selesai harus lebih besar dari jam mulai');
            }

            if (Shift::where('name', $validated['name'])->exists()) {
                throw new \Exception('Nama shift sudah digunakan');
            }

            $shift = Shift::create($validated);

            DB::commit();
            Cache::forget('master_shifts_active');

            $result = [
                'success' => true,
                'message' => 'Shift berhasil ditambahkan',
                'data' => $shift,
            ];

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
            DB::rollBack();
            Log::error('Error creating shift: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $shift = Shift::with(['workerShifts', 'dayTimes'])->withCount('workerShifts')->find($id);
            if (!$shift) {
                throw new \Exception('Shift tidak ditemukan');
            }
            $statistics = $this->getShiftStatistics($shift);

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
            $shift = Shift::with(['workerShifts', 'dayTimes'])->withCount('workerShifts')->find($id);
            if (!$shift) {
                throw new \Exception('Shift tidak ditemukan');
            }
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
            DB::beginTransaction();

            $shift = Shift::with(['workerShifts', 'dayTimes'])->withCount('workerShifts')->find($id);
            if (!$shift) {
                throw new \Exception('Shift tidak ditemukan');
            }

            if (!$this->isValidTimeRange($validated['start_time'], $validated['end_time'])) {
                throw new \Exception('Jam selesai harus lebih besar dari jam mulai');
            }

            $existingByName = Shift::where('name', $validated['name'])->first();
            if ($existingByName && $existingByName->id !== $id) {
                throw new \Exception('Nama shift sudah digunakan');
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['is_overnight'] = $request->has('is_overnight') ? true : false;
            $shift->update($validated);

            DB::commit();
            Cache::forget('master_shifts_active');

            $result = [
                'success' => true,
                'message' => 'Shift berhasil diperbarui',
                'data' => $shift,
            ];

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
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $shift = Shift::with(['workerShifts', 'dayTimes'])->withCount('workerShifts')->find($id);
            if (!$shift) {
                throw new \Exception('Shift tidak ditemukan');
            }

            if ($shift->workerShifts()->exists()) {
                throw new \Exception('Shift tidak dapat dihapus karena masih digunakan oleh pegawai');
            }

            $shift->delete();

            DB::commit();
            Cache::forget('master_shifts_active');

            $result = [
                'success' => true,
                'message' => 'Shift berhasil dihapus',
            ];

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.shifts.index')
                    ->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function isValidTimeRange(string $startTime, string $endTime): bool
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        if ($end->lessThan($start)) {
            return true;
        }

        return $end->greaterThan($start);
    }

    private function getShiftStatistics(Shift $shift): array
    {
        return [
            'total_workers' => $shift->workerShifts()->count(),
            'active_workers' => $shift->workerShifts()->where('is_active', true)->count(),
            'total_attendances_today' => $shift->attendances()
                ->whereDate('attendance_date', now())
                ->count(),
        ];
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
