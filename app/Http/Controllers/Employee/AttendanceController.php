<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceService;
use App\Services\Master\LocationService;
use App\Services\Export\PdfExportService;
use App\DTOs\AttendanceDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected LocationService $locationService,
        protected PdfExportService $pdfExportService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display employee's attendance history
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $filters = [
            'worker_id' => $worker->id,
            'date_from' => $request->date_from ?? now()->startOfMonth()->format('Y-m-d'),
            'date_to' => $request->date_to ?? now()->endOfMonth()->format('Y-m-d'),
            'status' => $request->status,
            'search' => $request->search,
            'per_page' => $request->per_page ?? 15,
        ];

        $attendances = $this->attendanceService->getAll($filters);
        
        // Get attendance summary for current month
        $monthlySummary = $this->attendanceService->getAll([
            'worker_id' => $worker->id,
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->endOfMonth()->format('Y-m-d'),
        ]);
        
        $summary = [
            'total_days' => $monthlySummary->total(),
            'present' => $monthlySummary->where('status', 'Hadir')->count(),
            'late' => $monthlySummary->where('status', 'Terlambat')->count(),
            'absent' => $monthlySummary->where('status', 'Tidak Hadir')->count(),
        ];

        return view('employee.attendance.index', compact('attendances', 'filters', 'summary', 'worker'));
    }

    /**
     * Show check-in form
     */
    public function checkInForm()
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        // Check if already checked in today
        $today = now()->format('Y-m-d');
        $existingAttendance = $this->attendanceService->getAll([
            'worker_id' => $worker->id,
            'date_from' => $today,
            'date_to' => $today,
        ])->first();

        if ($existingAttendance && $existingAttendance->check_in) {
            return redirect()->route('employee.attendance.index')
                ->with('error', 'Anda sudah melakukan check-in hari ini.');
        }

        $locations = $this->locationService->getAllActive();

        return view('employee.attendance.check-in', compact('locations'));
    }

    /**
     * Process check-in
     */
    public function checkIn(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $validated = $request->validate([
            'location_id' => 'required|uuid|exists:locations,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'notes' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            // Service checkIn expects array, not DTO
            $data = [
                'worker_id' => $worker->id,
                'location_id' => $validated['location_id'],
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'notes' => $validated['notes'] ?? null,
                'photo' => $request->file('photo'),
            ];

            $attendance = $this->attendanceService->checkIn($data);

            return redirect()->route('employee.attendance.index')
                ->with('success', 'Check-in berhasil!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal check-in: ' . $e->getMessage());
        }
    }

    /**
     * Process check-out
     */
    public function checkOut(Request $request, string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $attendance = $this->attendanceService->getById($id);

            // Verify this attendance belongs to the logged-in worker
            if ($attendance->worker_id !== $worker->id) {
                abort(403, 'Unauthorized');
            }

            $data = [
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'notes' => $validated['notes'] ?? null,
                'photo' => $request->file('photo'),
            ];

            $this->attendanceService->checkOut($id, $data);

            return redirect()->route('employee.attendance.index')
                ->with('success', 'Check-out berhasil!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal check-out: ' . $e->getMessage());
        }
    }

    /**
     * Show attendance detail
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $attendance = $this->attendanceService->getById($id);

        // Verify this attendance belongs to the logged-in worker
        if ($attendance->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        return view('employee.attendance.show', compact('attendance'));
    }

    /**
     * Export attendance to PDF
     */
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $filters = [
            'worker_id' => $worker->id,
            'date_from' => $request->date_from ?? now()->startOfMonth()->format('Y-m-d'),
            'date_to' => $request->date_to ?? now()->endOfMonth()->format('Y-m-d'),
            'status' => $request->status,
            'search' => $request->search,
        ];

        // Get all records without pagination for PDF
        $attendances = $this->attendanceService->getAll(array_merge($filters, ['per_page' => 10000]))->items();

        return $this->pdfExportService->exportAttendanceReport($attendances, $worker, $filters);
    }
}
