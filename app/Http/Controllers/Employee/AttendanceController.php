<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendancePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display employee's attendance history
     */
    public function index(Request $request)
    {
        $user = Auth::user();
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

        // Load worker relationships needed for shift display in virtual absent rows
        $worker->load('department', 'shift', 'workerShifts.shift', 'shiftOverrides.shift');

        // Get real attendance records as a collection for the filter range
        $realAttendances = $this->getAttendanceCollectionByPeriod(
            $worker->id,
            $filters['date_from'],
            $filters['date_to'],
            array_filter(['status' => $filters['status'], 'search' => $filters['search']])
        );

        // Compute virtual absent days: past work days in range with no attendance record
        $virtualAbsents = $this->computeVirtualAbsentDays(
            $worker,
            $filters['date_from'],
            $filters['date_to'],
            $realAttendances
        );

        // Capture count before optional status-filter might clear the collection
        $periodVirtualAbsentCount = $virtualAbsents->count();

        // When filtering by a specific status other than absent, exclude virtual absent rows
        if (!empty($filters['status']) && $filters['status'] !== 'absent') {
            $virtualAbsents = collect();
        }

        // Build merged, date-sorted, paginated attendance list
        $allRecords = $realAttendances->concat($virtualAbsents)->sortByDesc('attendance_date');
        $page = max(1, (int) $request->get('page', 1));
        $perPage = (int) ($filters['per_page'] ?? 15);
        $attendances = new \Illuminate\Pagination\LengthAwarePaginator(
            $allRecords->forPage($page, $perPage)->values(),
            $allRecords->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Ringkasan absensi untuk periode yang dipilih
        $filterStart = \Carbon\Carbon::parse($filters['date_from']);
        $monthlySummary = $this->getMonthlyAttendanceReport(
            $worker->id,
            $filterStart->month,
            $filterStart->year
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
            // Total hari kerja = DB records + hari tidak hadir tanpa catatan
            'total_days' => $monthlySummary->count() + $periodVirtualAbsentCount,
            'present' => $monthlySummary->whereIn('status', ['present', 'late'])->count(),
            'late' => $lateCount,
            'early_leave' => $earlyLeaveCount,
            'perfect' => $perfectCount,
            // Tidak hadir = recorded absents + hari tanpa catatan sama sekali
            'absent' => $monthlySummary->whereIn('status', ['absent', 'sick', 'permission', 'leave'])->count() + $periodVirtualAbsentCount,
        ];

        // Cek apakah ada sesi absensi yang aktif (Check In tapi belum Check Out)
        $today = now()->format('Y-m-d');
        $activeAttendance = $this->getAttendances([
            'worker_id' => $worker->id,
            'date_from' => $today,
            'date_to' => $today,
        ])->first();

        // Jika hari ini kosong atau sudah checkout, cek shift malam dari kemarin
        if (!$activeAttendance || $activeAttendance->check_out || ($activeAttendance && $activeAttendance->status !== 'present')) {
            $yesterday = now()->subDay()->format('Y-m-d');
            $prevAttendance = $this->getAttendances([
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
            $isWorkerOffDay = $worker->isOffDay(now());
            if ($isWorkerOffDay) {
                $todayOffInfo = [
                    'type' => 'off_day',
                    'title' => 'Hari Libur Anda',
                    'reason' => 'Hari libur sesuai jadwal kerja Anda',
                ];
            }
        }

        // Shift efektif hari ini (sudah termasuk override/tukar shift jika ada)
        $todayShiftInfo = $worker->resolveShiftForDate($today);

        return view('employee.attendance.index', compact('attendances', 'filters', 'summary', 'worker', 'activeAttendance', 'todayOffInfo', 'todayShiftInfo', 'filterStart'));
    }

    /**
     * Show check-in form
     */
    public function checkInForm()
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        // Check if already checked in today
        $today = now()->format('Y-m-d');
        $existingAttendance = $this->getAttendances([
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
        if ($worker->isOffDay(now())) {
            return redirect()->route('employee.attendance.index')
                ->with('info', 'Hari ini adalah hari libur Anda sesuai jadwal kerja. Tidak perlu melakukan absensi.');
        }

        // Check if on business trip today
        $activeBusinessTrip = \App\Models\BusinessTrip::where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        // Shift efektif hari ini (sudah termasuk tukar shift yang dieksekusi)
        $todayShiftInfo = $worker->resolveShiftForDate($today);

        $locations = collect([(object) $this->getConfiguredLocation()]);

        return view('employee.attendance.check-in', compact('locations', 'activeBusinessTrip', 'todayShiftInfo'));
    }

    /**
     * Show check-out form
     */
    public function checkOutForm()
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        // Cari absensi aktif (sudah check-in tapi belum check-out)
        $today = now()->format('Y-m-d');

        // 1. Cek hari ini
        $attendance = $this->getAttendances([
            'worker_id' => $worker->id,
            'date_from' => $today,
            'date_to' => $today,
        ])->first();

        // 2. Jika tidak ada atau sudah checkout, cek hari kemarin (untuk shift malam/overnight)
        if (!$attendance || $attendance->check_out) {
            $yesterday = now()->subDay()->format('Y-m-d');
            $prevAttendance = $this->getAttendances([
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

        $locations = collect([(object) $this->getConfiguredLocation()]);

        // Shift yang berlaku pada tanggal attendance (mendukung overnight + tukar shift)
        $attendanceShiftInfo = $worker->resolveShiftForDate($attendance->attendance_date);

        $checkoutWindowInfo = null;
        $effectiveSchedule = $attendanceShiftInfo['schedule'] ?? null;

        if (is_array($effectiveSchedule) && !empty($effectiveSchedule['end_time'])) {
            $attendanceDate = \Carbon\Carbon::parse($attendance->attendance_date);
            $shiftEndDateTime = \Carbon\Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $effectiveSchedule['end_time']);

            if (!empty($effectiveSchedule['is_overnight'])) {
                $shiftEndDateTime->addDay();
            }

            $checkOutWindowAfterMinutes = (int) round((float) config('attendance.check_out_window_after_hours', 1.5) * 60);
            $maxCheckoutTime = $shiftEndDateTime->copy()->addMinutes($checkOutWindowAfterMinutes);
            $now = now();

            $isPastShiftEnd = $now->greaterThan($shiftEndDateTime);
            $isPastCheckoutWindow = $now->greaterThan($maxCheckoutTime);

            $checkoutWindowInfo = [
                'now' => $now,
                'shift_end_time' => $shiftEndDateTime,
                'max_checkout_time' => $maxCheckoutTime,
                'is_past_shift_end' => $isPastShiftEnd,
                'is_past_checkout_window' => $isPastCheckoutWindow,
                'minutes_past_shift_end' => $isPastShiftEnd ? $shiftEndDateTime->diffInMinutes($now) : 0,
                'minutes_to_shift_end' => !$isPastShiftEnd ? $now->diffInMinutes($shiftEndDateTime) : 0,
                'minutes_to_checkout_deadline' => !$isPastCheckoutWindow ? $now->diffInMinutes($maxCheckoutTime) : 0,
            ];
        }

        return view('employee.attendance.check-out', compact('locations', 'attendance', 'attendanceShiftInfo', 'checkoutWindowInfo'));
    }

    /**
     * Process check-in
     */
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'photo' => 'required|string',
            'status' => 'required|in:present,sick,permission,leave',
            'attachment' => 'required_if:status,sick,permission|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            // Check if worker has off-day for today
            if ($worker->isOffDay(now())) {
                return back()->withInput()->with('error', 'Maaf, hari ini Anda libur. Alasan: Hari libur terjadwal');
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
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'accuracy' => $accuracy,
                'notes' => $validated['notes'] ?? null,
                'photo' => $photoFile,
                'status' => $validated['status'],
                'attachment' => $request->file('attachment'),
            ];

            $attendance = $this->performCheckIn($data);

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
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $attendance = $this->getAttendanceById($id);
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
        if ($worker->isOffDay(now()) && !$worker->canCheckOutOnDate(now(), $attendance->attendance_date)) {
            return back()->withInput()->with('error', 'Tidak dapat check-out hari ini. Alasan: Status hari libur');
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'photo' => 'required|string',
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
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'accuracy' => $accuracy,
                'notes' => $validated['notes'] ?? null,
                'photo' => $photoFile,
            ];

            $updatedAttendance = $this->performCheckOut($id, $data);

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
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $attendance = $this->getAttendanceById($id);

        // Verify this attendance belongs to the logged-in worker
        if ($attendance->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        // Load relationships
        $attendance->load([
            'photos',
            'worker.workerShifts.shift'
        ]);

        return view('employee.attendance.show', compact('attendance'));
    }

    /**
     * Serve attendance photo for the logged-in employee.
     */
    public function photo(string $id, string $type)
    {
        if (!in_array($type, ['check_in', 'check_out'], true)) {
            abort(404);
        }

        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            abort(404);
        }

        $attendance = $this->getAttendanceById($id);
        if (!$attendance || $attendance->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        $photo = $attendance->photos()
            ->where('photo_type', $type)
            ->orderByDesc('taken_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$photo || !Storage::disk('public')->exists($photo->photo_path)) {
            abort(404, 'Foto tidak ditemukan.');
        }

        $absolutePath = Storage::disk('public')->path($photo->photo_path);

        return response()->file($absolutePath, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Export attendance to PDF/Excel/CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $hasDateFilter = $request->filled('date_from') || $request->filled('date_to');

        if ($hasDateFilter) {
            $startDate = $request->filled('date_from')
                ? \Carbon\Carbon::parse($request->input('date_from'))->startOfDay()
                : now()->startOfMonth();
            $endDate = $request->filled('date_to')
                ? \Carbon\Carbon::parse($request->input('date_to'))->endOfDay()
                : now()->endOfMonth();
        } else {
            $exportMonth = $request->input('export_month');
            if ($exportMonth && preg_match('/^\d{4}-\d{2}$/', $exportMonth)) {
                $selectedMonth = \Carbon\Carbon::createFromFormat('Y-m', $exportMonth)->startOfMonth();
            } else {
                $selectedMonth = now()->startOfMonth();
            }
            $startDate = $selectedMonth->copy()->startOfMonth();
            // Without explicit date filter, cap at today
            $endDate = min($selectedMonth->copy()->endOfMonth(), now()->endOfDay());
        }

        // Map status dari form value (English) ke display label (Indonesian) yang dipakai di rows
        $statusMap = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'leave' => 'Cuti',
            'sick' => 'Sakit',
            'permission' => 'Izin',
        ];

        $statusFilter = $request->status ? ($statusMap[$request->status] ?? $request->status) : null;
        $searchFilter = $request->search;

        $filters = [
            'worker_id' => $worker->id,
            'date_from' => $startDate->format('Y-m-d'),
            'date_to' => $endDate->format('Y-m-d'),
            'status' => $statusFilter,
            'search' => $searchFilter,
        ];

        $rows = $this->buildMonthlyExportRows($worker, $startDate, $endDate);

        // Apply filters to rows
        $rowsCollection = collect($rows);

        // Filter by status if provided
        if (!empty($statusFilter)) {
            $rowsCollection = $rowsCollection->filter(function ($row) use ($statusFilter) {
                // Filter "Hadir" mencakup "Hadir" dan "Terlambat"
                if ($statusFilter === 'Hadir') {
                    return in_array($row['status'], ['Hadir', 'Terlambat']);
                }
                // Filter lainnya tetap exact match
                return $row['status'] === $statusFilter;
            });
        }

        // Filter by search term if provided (search in notes, location, shift name)
        if (!empty($searchFilter)) {
            $searchLower = strtolower($searchFilter);
            $rowsCollection = $rowsCollection->filter(function ($row) use ($searchLower) {
                return str_contains(strtolower($row['notes']), $searchLower) ||
                       str_contains(strtolower($row['location']), $searchLower) ||
                       str_contains(strtolower($row['shift_name']), $searchLower);
            });
        }

        $rows = $rowsCollection->values()->toArray();

        // Hitung summary berdasarkan data yang sudah difilter
        $rowsCollection = collect($rows);
        $summary = [
            'total' => $rowsCollection->count(),
            'present' => $rowsCollection->whereIn('status', ['Hadir', 'Terlambat'])->count(),
            'late' => $rowsCollection->where('status', 'Terlambat')->count(),
            'absent' => $rowsCollection->where('status', 'Tidak Hadir')->count(),
            'leave' => $rowsCollection->where('status', 'Cuti')->count(),
            'sick' => $rowsCollection->where('status', 'Sakit')->count(),
            'permission' => $rowsCollection->where('status', 'Izin')->count(),
            'no_data' => $rowsCollection->where('status', 'Belum Ada Data')->count(),
        ];

        $format = $request->input('format', 'pdf');
        $filename = 'absensi_' . $worker->nip . '_' . now()->format('Y-m-d');

        if ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\WorkerAttendanceCalendarExport($worker, $rows, $startDate, $endDate),
                $filename . '.xlsx'
            );
        }

        if ($format === 'csv') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\WorkerAttendanceCalendarExport($worker, $rows, $startDate, $endDate),
                $filename . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        }

        // Default PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('employee.exports.attendance-pdf', [
            'title' => 'Laporan Riwayat Absensi',
            'worker' => $worker,
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters,
            'summary' => $summary,
            'generated_at' => now()->format('d F Y H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export attendance to PDF (legacy route)
     */
    public function exportPdf(Request $request)
    {
        $request->merge(['format' => 'pdf']);
        return $this->export($request);
    }

    /**
     * Compute virtual "Tidak Hadir" days: past work days within range that have no attendance record.
     * Excludes: today & future, existing records, off-days, holidays, approved leaves, approved trips.
     */
    private function computeVirtualAbsentDays($worker, string $dateFrom, string $dateTo, $existingAttendances): \Illuminate\Support\Collection
    {
        $start  = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $end    = \Carbon\Carbon::parse($dateTo)->endOfDay();
        $today  = now()->startOfDay();

        // Cap end at yesterday — today is still open for check-in
        if ($end->gte($today)) {
            $end = $today->copy()->subDay()->endOfDay();
        }

        if ($start->gt($end)) {
            return collect();
        }

        // Existing attendance dates (real records)
        $existingDates = $existingAttendances->map(fn($a) =>
            \Carbon\Carbon::parse($a->attendance_date)->format('Y-m-d')
        )->flip();

        // Off days from worker's pattern in range
        $offDays = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($worker->isOffDay($date)) {
                $offDays[] = $date->format('Y-m-d');
            }
        }
        $offDays = array_flip($offDays);

        // Approved leaves in range
        $approvedLeaves = \App\Models\LeaveRequest::where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end->format('Y-m-d'))
            ->whereDate('end_date', '>=', $start->format('Y-m-d'))
            ->get();

        // Approved business trips in range
        $approvedTrips = \App\Models\BusinessTrip::where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end->format('Y-m-d'))
            ->whereDate('end_date', '>=', $start->format('Y-m-d'))
            ->get();

        // National holidays
        $holidays = \App\Models\Holiday::where('is_national', true)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()->keyBy(fn($h) => \Carbon\Carbon::parse($h->date)->format('Y-m-d'));

        $requiresHolidayAttendance = (bool) ($worker->department?->requires_holiday_attendance ?? false);

        $virtualDays = collect();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');

            // Skip days that already have an attendance record
            if (isset($existingDates[$dateKey])) {
                continue;
            }

            // Skip off-days (worker's scheduled rest)
            if (isset($offDays[$dateKey])) {
                continue;
            }

            // Skip national holidays (unless dept requires attendance)
            if (!$requiresHolidayAttendance && isset($holidays[$dateKey])) {
                continue;
            }

            // Skip approved leave days
            $onLeave = $approvedLeaves->first(fn($l) =>
                $date->between(
                    \Carbon\Carbon::parse($l->start_date)->startOfDay(),
                    \Carbon\Carbon::parse($l->end_date)->endOfDay()
                )
            );
            if ($onLeave) {
                continue;
            }

            // Skip approved business trip days
            $onTrip = $approvedTrips->first(fn($t) =>
                $date->between(
                    \Carbon\Carbon::parse($t->start_date)->startOfDay(),
                    \Carbon\Carbon::parse($t->end_date)->endOfDay()
                )
            );
            if ($onTrip) {
                continue;
            }

            // Check if the worker has a shift scheduled on this day
            $hasShift = method_exists($worker, 'getShiftForDate')
                ? !empty($worker->getShiftForDate($date->toDateTime()))
                : true; // default: assume work day if no method

            if (!$hasShift) {
                continue;
            }

            // This is a real unrecorded work day → virtual absent
            $virtualDay = new \stdClass();
            $virtualDay->is_virtual     = true;
            $virtualDay->id             = null;
            $virtualDay->worker_id      = $worker->id;
            $virtualDay->attendance_date = $dateKey;
            $virtualDay->status         = 'absent';
            $virtualDay->check_in       = null;
            $virtualDay->check_out      = null;
            $virtualDay->is_late        = false;
            $virtualDay->late_minutes   = 0;
            $virtualDay->is_early_leave = false;
            $virtualDay->early_leave_minutes = 0;
            $virtualDay->notes          = null;
            $virtualDay->worker         = $worker;

            $virtualDays->push($virtualDay);
        }

        return $virtualDays;
    }

    private function buildMonthlyExportRows($worker, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $worker->loadMissing(['department', 'workerShifts.shift.dayTimes', 'shiftOverrides.shift']);

        $today = now()->startOfDay();

        $attendances = \App\Models\Attendance::with(['shift'])
            ->where('worker_id', $worker->id)
            ->whereDate('attendance_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('attendance_date', '<=', $endDate->format('Y-m-d'))
            ->get();

        $attendanceByDate = $attendances->keyBy(function ($attendance) {
            return \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d');
        });

        $leaveRequests = \App\Models\LeaveRequest::with('leaveType')
            ->where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $endDate->format('Y-m-d'))
            ->whereDate('end_date', '>=', $startDate->format('Y-m-d'))
            ->get();

        $holidays = \App\Models\Holiday::where('is_national', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($holiday) {
                return \Carbon\Carbon::parse($holiday->date)->format('Y-m-d');
            });

        $requiresHolidayAttendance = (bool) ($worker->department?->requires_holiday_attendance ?? false);

        $rows = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $attendance = $attendanceByDate->get($dateKey);
            $holiday = $holidays->get($dateKey);
            $isHoliday = !is_null($holiday);
            $isOffDay = method_exists($worker, 'isOffDay') ? $worker->isOffDay($date->toDateTime()) : false;
            $isFutureDate = $date->gt($today);

            $shiftId = method_exists($worker, 'getShiftForDate')
                ? $worker->getShiftForDate($date->toDateTime())
                : null;
            $shift = $shiftId ? \App\Models\Shift::with('dayTimes')->find($shiftId) : null;

            $shiftTime = '-';
            if ($shift) {
                $schedule = $shift->getScheduleForDate($date->toDateTime());
                $shiftTime = \Carbon\Carbon::parse($schedule['start_time'])->format('H:i')
                    . ' - '
                    . \Carbon\Carbon::parse($schedule['end_time'])->format('H:i');
            }

            $leaveRequest = $leaveRequests->first(function ($leave) use ($date) {
                return $date->between(
                    \Carbon\Carbon::parse($leave->start_date)->startOfDay(),
                    \Carbon\Carbon::parse($leave->end_date)->endOfDay()
                );
            });

            $status = 'Tidak Hadir';
            $notes = '-';
            $checkIn = '-';
            $checkOut = '-';
            $lateInfo = '-';
            $earlyLeaveInfo = '-';
            $locationName = '-';

            if ($attendance) {
                $status = match ($attendance->status) {
                    'present' => $attendance->is_late ? 'Terlambat' : 'Hadir',
                    'late' => 'Terlambat',
                    'absent' => 'Tidak Hadir',
                    'leave' => 'Cuti',
                    'sick' => 'Sakit',
                    'permission' => 'Izin',
                    default => ucfirst($attendance->status),
                };

                $checkIn = $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-';
                $checkOut = $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-';
                $lateInfo = ($attendance->is_late && (int) $attendance->late_minutes > 0)
                    ? ((int) $attendance->late_minutes . ' menit')
                    : '-';
                $earlyLeaveInfo = ($attendance->is_early_leave && (int) $attendance->early_leave_minutes > 0)
                    ? ((int) $attendance->early_leave_minutes . ' menit')
                    : '-';
                $locationName = $this->getConfiguredLocation()['name'];
                $notes = $attendance->notes ?: '-';
            } elseif ($leaveRequest) {
                $leaveName = $leaveRequest->leaveType->name ?? 'Cuti';
                $leaveNameLower = strtolower($leaveName);

                if (str_contains($leaveNameLower, 'sakit')) {
                    $status = 'Sakit';
                } elseif (str_contains($leaveNameLower, 'izin')) {
                    $status = 'Izin';
                } else {
                    $status = 'Cuti';
                }

                $notes = $leaveName;
            } elseif ($isFutureDate) {
                $status = 'Belum Ada Data';
                $notes = 'Tanggal belum terlewati';
            } else {
                $isHolidayOff = $isHoliday && !$requiresHolidayAttendance;
                $isWorkday = !empty($shiftId) && !$isOffDay && !$isHolidayOff;

                if ($isWorkday) {
                    $status = 'Tidak Hadir';
                } else {
                    $status = 'Libur';
                    if ($isOffDay) {
                        $notes = 'Libur Off-day';
                    } elseif ($isHolidayOff) {
                        $notes = 'Libur Nasional: ' . ($holiday->name ?? '-');
                    } elseif (empty($shiftId)) {
                        $notes = 'Libur (Tidak ada jadwal shift)';
                    }
                }
            }

            $rows[] = [
                'date' => $date->format('d/m/Y'),
                'day_name' => $date->translatedFormat('l'),
                'shift_name' => $shift?->name ?? '-',
                'shift_time' => $shiftTime,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => $status,
                'late' => $lateInfo,
                'early_leave' => $earlyLeaveInfo,
                'location' => $locationName,
                'notes' => $notes,
            ];
        }

        return $rows;
    }

    private function getConfiguredLocation(): array
    {
        return [
            'id' => 'env-location',
            'name' => (string) config('attendance.location.name', 'Lokasi Utama'),
            'latitude' => (float) config('attendance.location.latitude', 0),
            'longitude' => (float) config('attendance.location.longitude', 0),
            'radius' => (int) config('attendance.location.radius', 100),
            'enforce_geofence' => (bool) config('attendance.location.enforce_geofence', true),
        ];
    }

    private function getAttendances(array $filters = []): \Illuminate\Support\Collection
    {
        $query = Attendance::with(['worker.department', 'shift', 'photos']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('attendance_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('attendance_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = strtolower((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(notes) LIKE ?', ['%' . $search . '%'])
                    ->orWhereHas('worker', function ($workerQuery) use ($search) {
                        $workerQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                            ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $search . '%'])
                            ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $search . '%']);
                    });
            });
        }

        return $query->orderByDesc('attendance_date')->orderByDesc('check_in')->get();
    }

    private function getAttendanceCollectionByPeriod(string $workerId, string $dateFrom, string $dateTo, array $filters = []): \Illuminate\Support\Collection
    {
        $filters = array_merge($filters, [
            'worker_id' => $workerId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        return $this->getAttendances($filters);
    }

    private function getMonthlyAttendanceReport(string $workerId, int $month, int $year): \Illuminate\Support\Collection
    {
        return Attendance::with(['shift'])
            ->where('worker_id', $workerId)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->get();
    }

    private function getAttendanceById(string $id): ?Attendance
    {
        return Attendance::with(['photos', 'worker.workerShifts.shift', 'worker.department', 'shift'])
            ->find($id);
    }

    private function performCheckIn(array $data): Attendance
    {
        DB::beginTransaction();

        try {
            $workerId = (string) $data['worker_id'];
            $today = now()->format('Y-m-d');

            $worker = \App\Models\Worker::with('department')->find($workerId);
            if (!$worker) {
                throw new \Exception('Data pekerja tidak ditemukan.');
            }

            if ($worker->isOffDay(now())) {
                throw new \Exception('Hari ini termasuk hari libur Anda.');
            }

            $existing = Attendance::where('worker_id', $workerId)
                ->whereDate('attendance_date', $today)
                ->first();
            if ($existing) {
                throw new \Exception('Anda sudah melakukan check-in hari ini.');
            }

            $holiday = \App\Models\Holiday::where('is_national', true)
                ->whereDate('date', $today)
                ->first();
            if ($holiday) {
                $deptRequiresAttendance = $worker->department && $worker->department->requires_holiday_attendance;
                if (!$deptRequiresAttendance) {
                    throw new \Exception('Hari ini adalah libur nasional (' . $holiday->name . '). Anda tidak perlu melakukan absensi.');
                }
            }

            $approvedLeave = \App\Models\LeaveRequest::where('worker_id', $workerId)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
            if ($approvedLeave) {
                $leaveTypeName = $approvedLeave->leaveType->name ?? 'Cuti';
                throw new \Exception('Anda sedang cuti (' . $leaveTypeName . '). Tidak perlu melakukan absensi.');
            }

            $approvedBusinessTrip = \App\Models\BusinessTrip::where('worker_id', $workerId)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
            if ($approvedBusinessTrip) {
                throw new \Exception('Anda sedang dalam perjalanan dinas ke ' . $approvedBusinessTrip->destination . '. Tidak perlu melakukan absensi.');
            }

            $status = $data['status'] ?? 'present';
            if (in_array($status, ['sick', 'permission'], true) && empty($data['attachment'])) {
                throw new \Exception('Status sakit/izin wajib melampirkan dokumen pendukung.');
            }

            if (!empty($data['attachment'])) {
                $this->saveAttachment($data['attachment'], $workerId, $status);
            }

            $checkInTime = now();
            $shiftInfo = method_exists($worker, 'resolveShiftForDate')
                ? $worker->resolveShiftForDate($today)
                : ['shift' => null, 'schedule' => null];

            $shift = $shiftInfo['shift'] ?? null;
            $schedule = $shiftInfo['schedule'] ?? null;
            if (!$shift || !$schedule) {
                throw new \Exception('Tidak ada jadwal shift aktif untuk pegawai ini.');
            }

            $shiftStartDateTime = \Carbon\Carbon::parse($today . ' ' . $schedule['start_time']);
            if (!empty($schedule['is_overnight'])) {
                $shiftEndDateTimeToday = \Carbon\Carbon::parse($today . ' ' . $schedule['end_time']);
                if ($checkInTime->lessThan($shiftEndDateTimeToday)) {
                    $shiftStartDateTime = $shiftStartDateTime->copy()->subDay();
                }
            }

            if ($status === 'present') {
                $checkInWindowBeforeMinutes = (int) round((float) config('attendance.check_in_window_before_hours', 0.5) * 60);
                $earlyCheckInGraceMinutes = (int) config('attendance.early_checkin_grace_minutes', 30);
                $strictTimeWindow = (bool) config('attendance.strict_time_window', false);

                $earliestCheckInTime = $shiftStartDateTime->copy()->subMinutes($checkInWindowBeforeMinutes);
                $veryEarlyCheckInTime = $earliestCheckInTime->copy()->subMinutes($earlyCheckInGraceMinutes);

                if ($checkInTime->lessThan($veryEarlyCheckInTime)) {
                    $totalDiffMinutes = $checkInTime->diffInMinutes($shiftStartDateTime);
                    $hoursDiff = intdiv($totalDiffMinutes, 60);
                    $minutesDiff = $totalDiffMinutes % 60;
                    $windowHours = intdiv($checkInWindowBeforeMinutes, 60);
                    $windowMinutes = $checkInWindowBeforeMinutes % 60;
                    $windowText = trim(($windowHours > 0 ? $windowHours . ' jam ' : '') . ($windowMinutes > 0 ? $windowMinutes . ' menit' : ''));

                    $message = sprintf(
                        'Check-in terlalu dini! Anda mencoba check-in %d jam %d menit sebelum shift dimulai (pukul %s). Batas check-in paling awal adalah %s sebelum shift (pukul %s).',
                        $hoursDiff,
                        $minutesDiff,
                        $shiftStartDateTime->format('H:i'),
                        $windowText,
                        $earliestCheckInTime->format('H:i')
                    );

                    if ($strictTimeWindow) {
                        throw new \Exception($message);
                    }

                    Log::warning('Very early check-in attempt', [
                        'worker_id' => $workerId,
                        'check_in_time' => $checkInTime->format('Y-m-d H:i:s'),
                        'shift_start' => $shiftStartDateTime->format('Y-m-d H:i:s'),
                        'earliest_allowed' => $veryEarlyCheckInTime->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            $configuredLocation = $this->getConfiguredLocation();
            $distance = $this->calculateDistance(
                (float) $configuredLocation['latitude'],
                (float) $configuredLocation['longitude'],
                (float) $data['latitude'],
                (float) $data['longitude']
            );

            if ($status === 'present' && $distance > (float) $configuredLocation['radius']) {
                throw new \Exception('Anda berada di luar radius lokasi absensi. Silakan mendekat ke lokasi yang ditentukan.');
            }

            if ($status === 'present') {
                $graceTime = $shiftStartDateTime->copy()->addMinutes((int) ($shift->grace_period_minutes ?? 0));
                $isLate = $checkInTime->greaterThan($graceTime);
                $lateMinutes = $isLate ? $checkInTime->diffInMinutes($shiftStartDateTime) : 0;
            } else {
                $isLate = false;
                $lateMinutes = 0;
            }

            $attendanceBusinessDate = $shiftStartDateTime->copy()->format('Y-m-d');

            $attendance = Attendance::create([
                'worker_id' => $workerId,
                'shift_id' => $shift->id,
                'attendance_date' => $attendanceBusinessDate,
                'check_in' => $checkInTime->format('Y-m-d H:i:s'),
                'distance_check_in' => $distance,
                'check_in_by_admin' => false,
                'check_in_admin_id' => null,
                'status' => $status,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['photo']) && $data['photo']) {
                $photoPath = $this->savePhoto($data['photo'], 'check_in', $workerId);

                AttendancePhoto::create([
                    'attendance_id' => $attendance->id,
                    'photo_path' => $photoPath,
                    'photo_type' => 'check_in',
                    'taken_at' => $checkInTime,
                    'created_at' => $checkInTime,
                ]);
            }

            DB::commit();
            return $attendance->fresh(['photos', 'worker.workerShifts.shift', 'worker.department', 'shift']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function performCheckOut(string $attendanceId, array $data): Attendance
    {
        DB::beginTransaction();

        try {
            $attendance = $this->getAttendanceById($attendanceId);
            if (!$attendance) {
                throw new \Exception('Data absensi tidak ditemukan.');
            }

            if ($attendance->check_out) {
                throw new \Exception('Anda sudah melakukan check-out.');
            }

            if (!$attendance->check_in) {
                throw new \Exception('Anda belum melakukan check-in. Tidak dapat melakukan check-out.');
            }

            $checkOutTime = now();
            $shift = $attendance->shift;
            if (!$shift) {
                throw new \Exception('Jadwal shift tidak ditemukan.');
            }

            $schedule = $shift->getScheduleForDate($attendance->attendance_date);
            $shiftBaseDate = \Carbon\Carbon::parse($attendance->attendance_date)->startOfDay();
            $shiftStartDateTime = \Carbon\Carbon::parse($shiftBaseDate->format('Y-m-d') . ' ' . $schedule['start_time']);
            $shiftEndDateTime = \Carbon\Carbon::parse($shiftBaseDate->format('Y-m-d') . ' ' . $schedule['end_time']);

            if (!empty($schedule['is_overnight']) && $shiftEndDateTime->lessThanOrEqualTo($shiftStartDateTime)) {
                $shiftEndDateTime->addDay();
            }

            $checkOutWindowAfterMinutes = (int) round((float) config('attendance.check_out_window_after_hours', 1.5) * 60);
            $maxCheckoutTime = $shiftEndDateTime->copy()->addMinutes($checkOutWindowAfterMinutes);
            if ($checkOutTime->greaterThan($maxCheckoutTime)) {
                $hoursDiff = $shiftEndDateTime->diffInHours($checkOutTime);
                throw new \Exception(
                    "Check-out terlalu terlambat ({$hoursDiff} jam setelah shift berakhir pukul {$shiftEndDateTime->format('H:i')}). " .
                    "Batas checkout adalah {$maxCheckoutTime->format('d M Y H:i')}. " .
                    'Silakan hubungi admin untuk koreksi absensi.'
                );
            }

            $configuredLocation = $this->getConfiguredLocation();
            $distance = $this->calculateDistance(
                (float) $configuredLocation['latitude'],
                (float) $configuredLocation['longitude'],
                (float) $data['latitude'],
                (float) $data['longitude']
            );

            if ($attendance->status === 'present' && $distance > (float) $configuredLocation['radius']) {
                throw new \Exception('Anda berada di luar radius lokasi absensi. Silakan mendekat ke lokasi yang ditentukan.');
            }

            $isEarlyLeave = $checkOutTime->lessThan($shiftEndDateTime);
            $earlyLeaveMinutes = $isEarlyLeave ? $checkOutTime->diffInMinutes($shiftEndDateTime) : 0;

            if ($isEarlyLeave) {
                Log::warning('Early check-out detected', [
                    'worker_id' => $attendance->worker_id,
                    'attendance_id' => $attendanceId,
                    'scheduled_end' => $shiftEndDateTime->format('H:i'),
                    'actual_checkout' => $checkOutTime->format('H:i'),
                    'early_minutes' => $earlyLeaveMinutes,
                ]);
            }

            $existingNotes = trim((string) $attendance->notes);
            $noteLines = [];
            if ($isEarlyLeave) {
                $noteLines[] = "[SYSTEM] Pulang lebih awal: {$earlyLeaveMinutes} menit";
            }
            if (!empty($data['notes'])) {
                $noteLines[] = (string) $data['notes'];
            }
            $combinedNotes = trim(implode("\n", array_filter(array_merge([$existingNotes], $noteLines))));

            $attendance->update([
                'check_out' => $checkOutTime->format('Y-m-d H:i:s'),
                'distance_check_out' => $distance,
                'check_out_by_admin' => false,
                'check_out_admin_id' => null,
                'is_early_leave' => $isEarlyLeave,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'notes' => $combinedNotes,
            ]);

            if (isset($data['photo']) && $data['photo']) {
                $photoPath = $this->savePhoto($data['photo'], 'check_out', (string) $attendance->worker_id);

                AttendancePhoto::create([
                    'attendance_id' => $attendance->id,
                    'photo_path' => $photoPath,
                    'photo_type' => 'check_out',
                    'taken_at' => $checkOutTime,
                    'created_at' => $checkOutTime,
                ]);
            }

            DB::commit();
            return $attendance->fresh(['photos', 'worker.workerShifts.shift', 'worker.department', 'shift']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function savePhoto($photo, string $type, string $workerId): string
    {
        $ext = strtolower($photo->getClientOriginalExtension() ?? 'jpg');
        $filename = sprintf('%s_%s_%s.%s', $workerId, $type, now()->format('YmdHis'), $ext);

        try {
            if (class_exists('Intervention\\Image\\ImageManagerStatic')) {
                $img = call_user_func(['Intervention\\Image\\ImageManagerStatic', 'make'], $photo->getRealPath());
                $img->orientate();

                if ($img->width() > 800) {
                    $img->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                $encoded = (string) $img->encode($ext, 70);
                $path = 'attendance-photos/' . $filename;
                Storage::disk('public')->put($path, $encoded);

                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('Image processing failed, storing original: ' . $e->getMessage());
        }

        return $photo->storeAs('attendance-photos', $filename, 'public');
    }

    private function saveAttachment($attachment, string $workerId, string $status): string
    {
        $filename = sprintf(
            '%s_%s_attachment_%s.%s',
            $workerId,
            $status,
            now()->format('YmdHis'),
            $attachment->getClientOriginalExtension()
        );

        return $attachment->storeAs('attendance-attachments', $filename, 'public');
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2)
            + cos($latFrom) * cos($latTo)
            * sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
