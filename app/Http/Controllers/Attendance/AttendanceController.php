<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceService;
use App\Services\Worker\WorkerService;
use App\Services\Master\LocationService;
use App\DTOs\AttendanceDTO;
use App\Http\Requests\Attendance\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected WorkerService $workerService,
        protected LocationService $locationService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'status' => $request->status,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'worker_id' => $request->worker_id,
            'per_page' => $request->per_page ?? 15,
        ];

        $attendances = $this->attendanceService->getAll($filters);
        $workers = $this->workerService->getAllActive();

        return view('admin.attendance.index', compact('attendances', 'workers', 'filters'));
    }

    public function create()
    {
        $workers = $this->workerService->getAllActive();
        $locations = $this->locationService->getAllActive();
        
        // Format locations for JavaScript validation
        $locationsData = $locations->mapWithKeys(function($loc) {
            return [$loc->id => [
                'id' => $loc->id,
                'name' => $loc->name,
                'latitude' => (float)$loc->latitude,
                'longitude' => (float)$loc->longitude,
                'radius' => (int)$loc->radius,
                'enforce_geofence' => (bool)$loc->enforce_geofence
            ]];
        });

        return view('admin.attendance.create', compact('workers', 'locations', 'locationsData'));
    }

    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|uuid|exists:workers,id',
            'location_id' => 'required|uuid|exists:locations,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $attendance = $this->attendanceService->checkIn($validated);

            return redirect()
                ->route('admin.attendance.show', $attendance->id)
                ->with('success', 'Check-in berhasil dicatat');
        } catch (\Exception $e) {
            \Log::error('Check-in error: ' . $e->getMessage(), [
                'worker_id' => $request->worker_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function checkOut(Request $request, string $id)
    {
        $validated = $request->validate([
            'location_id' => 'required|uuid|exists:locations,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $attendance = $this->attendanceService->checkOut($id, $validated);

            return redirect()
                ->route('admin.attendance.show', $attendance->id)
                ->with('success', 'Check-out berhasil dicatat');
        } catch (\Exception $e) {
            \Log::error('Checkout error: ' . $e->getMessage(), [
                'attendance_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $attendance = $this->attendanceService->getById($id);

        if (!$attendance) {
            return redirect()
                ->route('admin.attendance.index')
                ->with('error', 'Data absensi tidak ditemukan');
        }

        $locations = $this->locationService->getAllActive();
        
        // Format locations for JavaScript validation
        $locationsData = $locations->mapWithKeys(function($loc) {
            return [$loc->id => [
                'id' => $loc->id,
                'name' => $loc->name,
                'latitude' => (float)$loc->latitude,
                'longitude' => (float)$loc->longitude,
                'radius' => (int)$loc->radius,
                'enforce_geofence' => (bool)$loc->enforce_geofence
            ]];
        });

        return view('admin.attendance.show', compact('attendance', 'locations', 'locationsData'));
    }

    public function edit(string $id)
    {
        $attendance = $this->attendanceService->getById($id);
        $workers = $this->workerService->getAllActive();
        $locations = $this->locationService->getAllActive();

        return view('admin.attendance.edit', compact('attendance', 'workers', 'locations'));
    }

    public function update(AttendanceRequest $request, string $id)
    {
        try {
            $this->attendanceService->update($id, $request->validated());

            return redirect()
                ->route('admin.attendance.show', $id)
                ->with('success', 'Data absensi berhasil diperbarui');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->attendanceService->delete($id);

            return redirect()
                ->route('admin.attendance.index')
                ->with('success', 'Data absensi berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function dailyReport(Request $request)
    {
        $date = $request->date ?? now()->format('Y-m-d');
        $filters = ['attendance_date' => $date];

        $attendances = $this->attendanceService->getAll($filters);

        return view('admin.attendance.report', compact('attendances', 'date'));
    }

    public function monthlyReport(Request $request)
    {
        $workerId = $request->worker_id ?? Auth::user()->worker_id;
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $report = $this->attendanceService->getMonthlyReport($workerId, $month, $year);
        $workers = $this->workerService->getAllActive();

        return view('admin.attendance.report', compact('report', 'workers', 'month', 'year'));
    }

    public function export(Request $request)
    {
        try {
            $filters = [
                'worker_id' => $request->input('worker_id'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'status' => $request->input('status'),
            ];

            $filename = 'laporan-absensi-' . now()->format('Y-m-d-His') . '.xlsx';
            
            return Excel::download(new AttendanceExport($filters), $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}