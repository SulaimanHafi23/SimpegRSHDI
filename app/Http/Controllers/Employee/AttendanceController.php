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

        // Load worker relationships needed for shift display in virtual absent rows
        $worker->load('department', 'shift', 'workerShifts.shift', 'shiftOverrides.shift');

        // Get real attendance records as a collection for the filter range
        $realAttendances = $this->attendanceService->getCollectionByPeriod(
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
        $monthlySummary = $this->attendanceService->getMonthlyReport(
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

        return view('employee.attendance.index', compact('attendances', 'filters', 'summary', 'worker', 'activeAttendance', 'todayOffInfo', 'filterStart'));
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

        $exportMonth = $request->input('export_month');
        if ($exportMonth && preg_match('/^\d{4}-\d{2}$/', $exportMonth)) {
            $selectedMonth = \Carbon\Carbon::createFromFormat('Y-m', $exportMonth)->startOfMonth();
        } else {
            $selectedMonth = now()->startOfMonth();
        }

        $startDate = $selectedMonth->copy()->startOfMonth();
        $endDate = $selectedMonth->copy()->endOfMonth();

        $filters = [
            'worker_id' => $worker->id,
            'date_from' => $startDate->format('Y-m-d'),
            'date_to' => $endDate->format('Y-m-d'),
            'status' => $request->status,
            'search' => $request->search,
            'export_month' => $selectedMonth->format('Y-m'),
        ];

        $rows = $this->buildMonthlyExportRows($worker, $startDate, $endDate);
        $rowsCollection = collect($rows);
        $summary = [
            'total' => $rowsCollection->count(),
            'present' => $rowsCollection->whereIn('status', ['Hadir', 'Terlambat'])->count(),
            'late' => $rowsCollection->where('status', 'Terlambat')->count(),
            'absent' => $rowsCollection->where('status', 'Tidak Hadir')->count(),
            'leave' => $rowsCollection->where('status', 'Cuti')->count(),
            'sick' => $rowsCollection->where('status', 'Sakit')->count(),
            'permission' => $rowsCollection->where('status', 'Izin')->count(),
        ];

        $format = $request->input('format', 'pdf');
        $filename = 'absensi_' . $worker->nip . '_' . $selectedMonth->format('Y-m');

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
        if (method_exists($this->offDayService, 'getOffDaysInRange')) {
            $offDays = array_flip($this->offDayService->getOffDaysInRange($worker, $start, $end));
        }

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

        $attendances = \App\Models\Attendance::with(['shift', 'location'])
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
                'notes' => $notes,
            ];
        }

        return $rows;
    }
}
