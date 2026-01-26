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

        // Map status label from UI (Hadir/Terlambat/Tidak Hadir) ke kode internal (present/late/absent)
        $statusMap = [
            'Hadir' => 'present',
            'Terlambat' => 'late',
            'Tidak Hadir' => 'absent',
        ];

        $mappedStatus = $request->status ? ($statusMap[$request->status] ?? $request->status) : null;

        $filters = [
            'worker_id' => $worker->id,
            'date_from' => $request->date_from ?? now()->startOfMonth()->format('Y-m-d'),
            'date_to' => $request->date_to ?? now()->endOfMonth()->format('Y-m-d'),
            'status' => $mappedStatus,
            'search' => $request->search,
            'per_page' => $request->per_page ?? 15,
        ];

        $attendances = $this->attendanceService->getAll($filters);

        // Ringkasan absensi untuk bulan berjalan menggunakan kode status internal
        $monthlySummary = $this->attendanceService->getMonthlyReport(
            $worker->id,
            now()->month,
            now()->year
        );

        $summary = [
            'total_days' => $monthlySummary->count(),
            'present' => $monthlySummary->where('status', 'present')->count(),
            'late' => $monthlySummary->where('status', 'late')->count(),
            // Semua status tidak hadir: absent, sick, permission, leave
            'absent' => $monthlySummary->whereIn('status', ['absent', 'sick', 'permission', 'leave'])->count(),
        ];

        // Cek apakah ada sesi absensi yang aktif (Check In tapi belum Check Out)
        $today = now()->format('Y-m-d');
        $activeAttendance = $this->attendanceService->getAll([
            'worker_id' => $worker->id,
            'date_from' => $today,
            'date_to' => $today,
        ])->first();

        // Jika hari ini kosong atau sudah checkout, cek shift malam dari kemarin
        if (!$activeAttendance || $activeAttendance->check_out || ($activeAttendance && $activeAttendance->status !== 'present')) {
            $yesterday = now()->subDay()->format('Y-m-d');
            $prevAttendance = $this->attendanceService->getAll([
                'worker_id' => $worker->id,
                'date_from' => $yesterday,
                'date_to' => $yesterday,
            ])->first();

            if ($prevAttendance && $prevAttendance->check_in && !$prevAttendance->check_out && $prevAttendance->status === 'present') {
                $activeAttendance = $prevAttendance;
            } else {
                $activeAttendance = null; // Tidak ada sesi aktif
            }
        }

        return view('employee.attendance.index', compact('attendances', 'filters', 'summary', 'worker', 'activeAttendance'));
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
     * Show check-out form
     */
    public function checkOutForm()
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        // Cari absensi aktif (sudah check-in tapi belum check-out)
        $today = now()->format('Y-m-d');

        // 1. Cek hari ini
        $attendance = $this->attendanceService->getAll([
            'worker_id' => $worker->id,
            'date_from' => $today,
            'date_to' => $today,
        ])->first();

        // 2. Jika tidak ada atau sudah checkout, cek hari kemarin (untuk shift malam/overnight)
        if (!$attendance || $attendance->check_out) {
            $yesterday = now()->subDay()->format('Y-m-d');
            $prevAttendance = $this->attendanceService->getAll([
                'worker_id' => $worker->id,
                'date_from' => $yesterday,
                'date_to' => $yesterday,
            ])->first();

            if ($prevAttendance && $prevAttendance->check_in && !$prevAttendance->check_out) {
                $attendance = $prevAttendance;
            }
        }

        if (!$attendance || !$attendance->check_in || $attendance->check_out) {
            return redirect()->route('employee.attendance.index')
                ->with('error', 'Tidak ada sesi check-in aktif yang perlu di-checkout.');
        }

        // Only allow check-out for 'present' attendance
        if ($attendance->status !== 'present') {
            return redirect()->route('employee.attendance.index')
                ->with('error', 'Absensi dengan status selain hadir tidak memerlukan check-out.');
        }

        $locations = $this->locationService->getAllActive();

        return view('employee.attendance.check-out', compact('locations', 'attendance'));
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
            'accuracy' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'photo' => 'nullable|string',
            'status' => 'required|in:present,sick,permission,leave',
        ]);

        try {
            // Server-side check for accuracy
            $maxAcc = config('attendance.max_accuracy', 300);
            $accuracy = $validated['accuracy'] ?? ($request->input('accuracy') ?? null);
            if ($accuracy !== null && is_numeric($accuracy) && $accuracy > $maxAcc) {
                return back()->withInput()->with('error', "Lokasi tidak cukup akurat (±{$accuracy} m). Silakan gunakan ponsel atau pilih lokasi manual.");
            }

            // Handle base64 photo
            $photoFile = null;
            if ($request->has('photo') && !empty($request->input('photo'))) {
                $photoData = $request->input('photo');
                if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
                    $photoData = substr($photoData, strpos($photoData, ',') + 1);
                    $type = strtolower($type[1]);
                    if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                        return back()->withInput()->with('error', 'Format foto tidak valid. Gunakan JPG atau PNG.');
                    }
                    $photoData = base64_decode($photoData);
                    if ($photoData === false) {
                        return back()->withInput()->with('error', 'Foto tidak valid.');
                    }
                    $tmpFile = tempnam(sys_get_temp_dir(), 'photo_');
                    file_put_contents($tmpFile, $photoData);
                    $photoFile = new \Illuminate\Http\UploadedFile(
                        $tmpFile,
                        'photo.' . $type,
                        'image/' . $type,
                        null,
                        true
                    );
                }
            }

            $data = [
                'worker_id' => $worker->id,
                'location_id' => $validated['location_id'],
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'accuracy' => $accuracy,
                'notes' => $validated['notes'] ?? null,
                'photo' => $photoFile,
                'status' => $validated['status'],
            ];

            $attendance = $this->attendanceService->checkIn($data);

            // Clean up temp file if created
            if ($photoFile && file_exists($photoFile->getRealPath())) {
                @unlink($photoFile->getRealPath());
            }

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

        // Prevent check-out for non-present statuses
        $attendance = $this->attendanceService->getById($id);
        if ($attendance && $attendance->status !== 'present') {
            return redirect()->route('employee.attendance.index')
                ->with('error', 'Absensi dengan status selain hadir tidak memerlukan check-out.');
        }

        $validated = $request->validate([
            'location_id' => 'required|uuid|exists:locations,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'photo' => 'nullable|string',
        ]);

        try {
            $attendance = $this->attendanceService->getById($id);

            // Verify this attendance belongs to the logged-in worker
            if ($attendance->worker_id !== $worker->id) {
                abort(403, 'Unauthorized');
            }

            // Server-side check for accuracy
            $maxAcc = config('attendance.max_accuracy', 300);
            $accuracy = $validated['accuracy'] ?? ($request->input('accuracy') ?? null);
            if ($accuracy !== null && is_numeric($accuracy) && $accuracy > $maxAcc) {
                return back()->withInput()->with('error', "Lokasi tidak cukup akurat (±{$accuracy} m). Silakan gunakan ponsel atau pilih lokasi manual.");
            }

            // Handle base64 photo
            $photoFile = null;
            if ($request->has('photo') && !empty($request->input('photo'))) {
                $photoData = $request->input('photo');
                if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
                    $photoData = substr($photoData, strpos($photoData, ',') + 1);
                    $type = strtolower($type[1]);
                    if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                        return back()->withInput()->with('error', 'Format foto tidak valid. Gunakan JPG atau PNG.');
                    }
                    $photoData = base64_decode($photoData);
                    if ($photoData === false) {
                        return back()->withInput()->with('error', 'Foto tidak valid.');
                    }
                    $tmpFile = tempnam(sys_get_temp_dir(), 'photo_');
                    file_put_contents($tmpFile, $photoData);
                    $photoFile = new \Illuminate\Http\UploadedFile(
                        $tmpFile,
                        'photo.' . $type,
                        'image/' . $type,
                        null,
                        true
                    );
                }
            }

            $data = [
                'worker_id' => $worker->id,
                'location_id' => $validated['location_id'],
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'accuracy' => $accuracy,
                'notes' => $validated['notes'] ?? null,
                'photo' => $photoFile,
            ];

            $this->attendanceService->checkOut($id, $data);

            // Clean up temp file if created
            if ($photoFile && file_exists($photoFile->getRealPath())) {
                @unlink($photoFile->getRealPath());
            }

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
