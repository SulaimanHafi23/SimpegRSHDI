<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceService;
use App\Services\Master\LocationService;
use App\Services\Export\PdfExportService;
use App\Services\WorkerOffDay\WorkerOffDayService;
use App\DTOs\AttendanceDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected LocationService $locationService,
        protected PdfExportService $pdfExportService,
        protected WorkerOffDayService $offDayService
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

        // Hitung jumlah yang terlambat (is_late = true)
        $lateCount = $monthlySummary->where('is_late', true)->count();

        // Hitung jumlah pulang cepat (is_early_leave = true)
        $earlyLeaveCount = $monthlySummary->where('is_early_leave', true)->count();

        // Hitung hadir sempurna (present, tidak terlambat, tidak pulang cepat)
        $perfectCount = $monthlySummary
            ->whereIn('status', ['present'])
            ->where('is_late', false)
            ->where('is_early_leave', false)
            ->count();

        $summary = [
            'total_days' => $monthlySummary->count(),
            'present' => $monthlySummary->whereIn('status', ['present', 'late'])->count(),
            'late' => $lateCount,
            'early_leave' => $earlyLeaveCount,
            'perfect' => $perfectCount,
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

        // ── Cek apakah hari ini pegawai libur / cuti / tanggal merah ──
        $todayOffInfo = null;
        $worker->load('department');

        // 1. Cek libur nasional (tanggal merah)
        $holiday = \App\Models\Holiday::where('is_national', true)
            ->whereDate('date', $today)
            ->first();
        if ($holiday) {
            $deptRequiresAttendance = $worker->department && $worker->department->requires_holiday_attendance;
            if (!$deptRequiresAttendance) {
                $todayOffInfo = [
                    'type' => 'holiday',
                    'title' => 'Hari Libur Nasional',
                    'reason' => $holiday->name . ($holiday->description ? ' — ' . $holiday->description : ''),
                ];
            }
        }

        // 2. Cek cuti yang disetujui
        if (!$todayOffInfo) {
            $approvedLeave = \App\Models\LeaveRequest::where('worker_id', $worker->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->with('leaveType')
                ->first();
            if ($approvedLeave) {
                $todayOffInfo = [
                    'type' => 'leave',
                    'title' => 'Sedang Cuti',
                    'reason' => ($approvedLeave->leaveType->name ?? 'Cuti')
                        . ' ('. \Carbon\Carbon::parse($approvedLeave->start_date)->format('d M')
                        . ' - ' . \Carbon\Carbon::parse($approvedLeave->end_date)->format('d M Y') . ')',
                ];
            }
        }

        // 3. Cek perjalanan dinas
        if (!$todayOffInfo) {
            $businessTrip = \App\Models\BusinessTrip::where('worker_id', $worker->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
            if ($businessTrip) {
                $todayOffInfo = [
                    'type' => 'business_trip',
                    'title' => 'Perjalanan Dinas',
                    'reason' => 'Dinas ke ' . $businessTrip->destination
                        . ' (' . \Carbon\Carbon::parse($businessTrip->start_date)->format('d M')
                        . ' - ' . \Carbon\Carbon::parse($businessTrip->end_date)->format('d M Y') . ')',
                ];
            }
        }

        // 4. Cek hari libur pola (off-day pattern / exception milik pegawai)
        if (!$todayOffInfo) {
            $isWorkerOffDay = $this->offDayService->isOffDay($worker, $today);
            if ($isWorkerOffDay) {
                $offDayDetail = $this->offDayService->getOffDayInfo($worker, $today);
                $todayOffInfo = [
                    'type' => 'off_day',
                    'title' => 'Hari Libur Anda',
                    'reason' => $offDayDetail['reason'] ?? 'Hari libur sesuai jadwal kerja Anda',
                ];
            }
        }

        return view('employee.attendance.index', compact('attendances', 'filters', 'summary', 'worker', 'activeAttendance', 'todayOffInfo'));
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

        // ── Cek hari libur sebelum menampilkan form check-in ──
        $worker->load('department');

        // Libur nasional (kecuali dept standby)
        $holiday = \App\Models\Holiday::where('is_national', true)
            ->whereDate('date', $today)->first();
        if ($holiday) {
            $deptRequires = $worker->department && $worker->department->requires_holiday_attendance;
            if (!$deptRequires) {
                return redirect()->route('employee.attendance.index')
                    ->with('info', 'Hari ini adalah libur nasional (' . $holiday->name . '). Anda tidak perlu melakukan absensi.');
            }
        }

        // Cuti yang disetujui
        $approvedLeave = \App\Models\LeaveRequest::where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with('leaveType')->first();
        if ($approvedLeave) {
            return redirect()->route('employee.attendance.index')
                ->with('info', 'Anda sedang cuti (' . ($approvedLeave->leaveType->name ?? 'Cuti') . '). Tidak perlu melakukan absensi.');
        }

        // Hari libur pola pegawai (off-day)
        if ($this->offDayService->isOffDay($worker, $today)) {
            return redirect()->route('employee.attendance.index')
                ->with('info', 'Hari ini adalah hari libur Anda sesuai jadwal kerja. Tidak perlu melakukan absensi.');
        }

        // Check if on business trip today
        $activeBusinessTrip = \App\Models\BusinessTrip::where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        $locations = $this->locationService->getAllActive();

        return view('employee.attendance.check-in', compact('locations', 'activeBusinessTrip'));
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
            'accuracy' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'photo' => 'nullable|string',
            'status' => 'required|in:present,sick,permission,leave',
        ]);

        try {
            // Check if worker has off-day for today
            $offDayCheck = $this->offDayService->canPerformAttendance(
                $worker,
                now()->format('Y-m-d'),
                'check_in'
            );
            
            if (!$offDayCheck['can_perform']) {
                return back()->withInput()->with('error', 'Maaf, hari ini Anda libur. Alasan: ' . ($offDayCheck['message'] ?? 'Hari libur terjadwal'));
            }

            // Server-side check for accuracy (always enforce for present status)
            $maxAcc = config('attendance.max_accuracy', 300);
            $accuracy = $validated['accuracy'];
            if ($validated['status'] === 'present' && $accuracy > $maxAcc) {
                return back()->withInput()->with('error', "Lokasi tidak cukup akurat (±{$accuracy} m). Maksimal akurasi yang diizinkan adalah ±{$maxAcc} m. Coba pindah ke area terbuka atau aktifkan GPS.");
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
                    if (strlen($photoData) > 2 * 1024 * 1024) {
                        return back()->withInput()->with('error', 'Ukuran foto terlalu besar. Maksimal 2MB.');
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

        $attendance = $this->attendanceService->getById($id);
        if (!$attendance) {
            return redirect()->route('employee.attendance.index')
                ->with('error', 'Data absensi tidak ditemukan.');
        }
        
        // Verify this attendance belongs to the logged-in worker
        if ($attendance->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        // Prevent check-out for non-present statuses
        if ($attendance && $attendance->status !== 'present') {
            return redirect()->route('employee.attendance.index')
                ->with('error', 'Absensi dengan status selain hadir tidak memerlukan check-out.');
        }

        // Check if attendance is already completed
        if ($attendance->check_out) {
            return redirect()->route('employee.attendance.index')
                ->with('warning', 'Anda sudah melakukan check-out untuk hari ini.');
        }

        // Smart off-day check for check-out with overnight shift support
        $checkOutDate = now()->format('Y-m-d');
        $offDayCheck = $this->offDayService->canPerformAttendance(
            $worker,
            $checkOutDate,
            'check_out',
            $attendance->attendance_date->format('Y-m-d')  // pass check-in date for overnight logic
        );
        
        if (!$offDayCheck['can_perform']) {
            return back()->withInput()->with('error', 'Tidak dapat check-out hari ini. Alasan: ' . ($offDayCheck['message'] ?? 'Status hari libur'));
        }

        $validated = $request->validate([
            'location_id' => 'required|uuid|exists:locations,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'photo' => 'nullable|string',
        ]);

        try {
            // Server-side check for accuracy
            $maxAcc = config('attendance.max_accuracy', 300);
            $accuracy = $validated['accuracy'];
            if ($accuracy > $maxAcc) {
                return back()->withInput()->with('error', "Lokasi tidak cukup akurat (±{$accuracy} m). Maksimal akurasi yang diizinkan adalah ±{$maxAcc} m. Coba pindah ke area terbuka atau aktifkan GPS.");
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
                    if (strlen($photoData) > 2 * 1024 * 1024) {
                        return back()->withInput()->with('error', 'Ukuran foto terlalu besar. Maksimal 2MB.');
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

            $updatedAttendance = $this->attendanceService->checkOut($id, $data);

            // Clean up temp file if created
            if ($photoFile && file_exists($photoFile->getRealPath())) {
                @unlink($photoFile->getRealPath());
            }

            // Check for early checkout and provide appropriate feedback
            $message = 'Check-out berhasil!';
            $alertType = 'success';

            if ($updatedAttendance->is_early_leave && $updatedAttendance->early_leave_minutes > 0) {
                $hours = floor($updatedAttendance->early_leave_minutes / 60);
                $minutes = $updatedAttendance->early_leave_minutes % 60;
                $earlyText = '';

                if ($hours > 0) {
                    $earlyText .= $hours . ' jam ';
                }
                if ($minutes > 0) {
                    $earlyText .= $minutes . ' menit';
                }

                $message = "Check-out berhasil! Catatan: Anda pulang lebih awal {$earlyText} dari jadwal. Pastikan sudah mendapat izin dari atasan.";
                $alertType = 'warning';
            }

            return redirect()->route('employee.attendance.index')
                ->with($alertType, $message);

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

        // Load relationships
        $attendance->load([
            'location',
            'photos',
            'worker.workerShifts.shift'
        ]);

        return view('employee.attendance.show', compact('attendance'));
    }

    /**
     * Export attendance to PDF/Excel/CSV
     */
    public function export(Request $request)
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

        // Get all records without pagination
        $attendances = $this->attendanceService->getAll(array_merge($filters, ['per_page' => 10000]))->items();

        $format = $request->input('format', 'pdf');

        if ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\EmployeeAttendanceExport(collect($attendances), $worker),
                'absensi_' . $worker->nip . '_' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        if ($format === 'csv') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\EmployeeAttendanceExport(collect($attendances), $worker),
                'absensi_' . $worker->nip . '_' . now()->format('Y-m-d') . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        }

        // Default PDF
        return $this->pdfExportService->exportAttendanceReport($attendances, $worker, $filters);
    }

    /**
     * Export attendance to PDF (legacy route)
     */
    public function exportPdf(Request $request)
    {
        $request->merge(['format' => 'pdf']);
        return $this->export($request);
    }
}
