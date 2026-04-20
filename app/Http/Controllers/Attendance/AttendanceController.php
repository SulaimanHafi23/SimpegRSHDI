<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendancePhoto;
use App\Models\Worker;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class AttendanceController extends Controller
{
    use DepartmentFilterable;
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:attendance.manage');
    }

    public function index(Request $request)
    {
        $departmentId = $this->getManagerDepartmentFilter();

        // Filter untuk riwayat absensi (tanpa default tanggal)
        $historyFilters = [
            'search' => $request->search,
            'status' => $request->status,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'worker_id' => $request->worker_id,
            'department_id' => $departmentId,
            'per_page' => $request->per_page ?? 15,
        ];

        // Ambil data riwayat absensi berdasarkan filter
        $attendances = $this->getAttendances($historyFilters);

        // Get workers from user's department if Manager
        if ($departmentId) {
            $workers = $this->getWorkersByDepartment($departmentId);
            $allWorkers = $this->getWorkersByDepartment($departmentId);
        } else {
            $workers = $this->getAllActiveWorkers();
            $allWorkers = $this->getAllActiveWorkers();
        }

        // Load relationships yang diperlukan
        $allWorkers->load([
            'shift', 'workerShifts.shift', 'shiftOverrides.shift', 'department',
            'shiftSwapRequestsAsRequester' => function ($q) {
                $q->where('status', 'executed')->with('targetWorker');
            },
            'shiftSwapRequestsAsTarget' => function ($q) {
                $q->where('status', 'executed')->with('requester');
            },
        ]);

        // Ambil tanggal yang dipilih untuk view "Absensi Hari Ini"
        $selectedDate = $request->attendance_date ?? now()->format('Y-m-d');

        // Ambil data absensi untuk tanggal yang dipilih (dengan department filter)
        $todayAttendances = $this->getAttendances([
            'date_from' => $selectedDate,
            'date_to' => $selectedDate,
            'department_id' => $departmentId,
            'per_page' => 1000, // Ambil semua data untuk tanggal tersebut
        ])->getCollection();

        // Pre-load semua leave requests untuk tanggal ini (1 query, bukan N query)
        $leaveRequestsByWorker = \App\Models\LeaveRequest::whereIn('worker_id', $allWorkers->pluck('id'))
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $selectedDate)
            ->whereDate('end_date', '>=', $selectedDate)
            ->with('leaveType')
            ->get()
            ->keyBy('worker_id');

        // Buat array workers dengan status absensi untuk tanggal yang dipilih
        $workersWithAttendance = $allWorkers->map(function ($worker) use ($todayAttendances, $selectedDate, $leaveRequestsByWorker) {
            $todayAttendance = $todayAttendances->firstWhere('worker_id', $worker->id);

            // Ambil leave request dari pre-loaded collection (tanpa query tambahan)
            $leaveRequest = $leaveRequestsByWorker->get($worker->id);

            $worker->today_attendance = $todayAttendance;
            $worker->leave_request = $leaveRequest;
            $worker->attendance_status = $todayAttendance ? $todayAttendance->status : 'not_checked_in';
            $worker->check_in_time = $todayAttendance ? $todayAttendance->check_in : null;
            $worker->check_out_time = $todayAttendance ? $todayAttendance->check_out : null;
            $worker->is_late = $todayAttendance ? $todayAttendance->is_late : false;
            $worker->late_minutes = $todayAttendance ? $todayAttendance->late_minutes : 0;
            $worker->is_early_leave = $todayAttendance ? $todayAttendance->is_early_leave : false;
            $worker->early_leave_minutes = $todayAttendance ? $todayAttendance->early_leave_minutes : 0;

            $isOffDay = method_exists($worker, 'isOffDay')
                ? $worker->isOffDay(\Carbon\Carbon::parse($selectedDate))
                : false;
            $worker->is_off_day = $isOffDay;

            if (!$todayAttendance && !$leaveRequest && $isOffDay) {
                $worker->attendance_status = 'off_day';
                $worker->status_label = 'Libur Kerja';
            }

            return $worker;
        });

        $allWorkersWithAttendance = $workersWithAttendance;

        // Apply filters to workersWithAttendance for "today" tab
        if ($request->input('worker_id')) {
            $workersWithAttendance = $workersWithAttendance->where('id', $request->input('worker_id'));
        }
        if ($request->input('search')) {
            $searchTerm = strtolower($request->input('search'));
            $workersWithAttendance = $workersWithAttendance->filter(function ($worker) use ($searchTerm) {
                return str_contains(strtolower($worker->name ?? ''), $searchTerm)
                    || str_contains(strtolower($worker->nip ?? ''), $searchTerm)
                    || str_contains(strtolower($worker->email ?? ''), $searchTerm);
            });
        }
        if ($request->input('status')) {
            $statusFilter = $request->input('status');
            $workersWithAttendance = $workersWithAttendance->filter(function ($worker) use ($statusFilter) {
                if ($statusFilter === 'late') {
                    return $worker->is_late;
                }
                return $worker->attendance_status === $statusFilter;
            });
        }
        $workersWithAttendance = $workersWithAttendance->values();

        // Hitung statistik untuk tanggal yang dipilih
        $summary = [
            'total_workers' => $allWorkers->count(),
            'present' => $allWorkersWithAttendance->whereIn('attendance_status', ['present', 'late'])->count(),
            'late' => $allWorkersWithAttendance->where('is_late', true)->count(),
            'early_leave' => $allWorkersWithAttendance->where('is_early_leave', true)->count(),
            'perfect' => $todayAttendances->whereIn('status', ['present'])
                ->where('is_late', false)
                ->where('is_early_leave', false)
                ->count(),
            'off_day' => $allWorkersWithAttendance->where('attendance_status', 'off_day')->count(),
            'on_leave' => $allWorkersWithAttendance->whereIn('attendance_status', ['leave', 'sick', 'permission'])->count(),
            'absent' => $allWorkersWithAttendance->where('attendance_status', 'not_checked_in')->count(),
        ];

        // Tentukan periode untuk statistik riwayat
        $statsPeriod = $request->stats_period ?? 'month'; // month, year, custom
        $statsMonth = $request->stats_month ?? now()->format('m');
        $statsYear = $request->stats_year ?? now()->format('Y');
        $statsDateFrom = $request->stats_date_from;
        $statsDateTo = $request->stats_date_to;

        // Hitung date range berdasarkan periode
        if ($statsPeriod === 'year') {
            $statsStartDate = \Carbon\Carbon::create($statsYear, 1, 1)->format('Y-m-d');
            $statsEndDate = \Carbon\Carbon::create($statsYear, 12, 31)->format('Y-m-d');
        } elseif ($statsPeriod === 'custom' && $statsDateFrom && $statsDateTo) {
            $statsStartDate = $statsDateFrom;
            $statsEndDate = $statsDateTo;
        } else {
            // Default: per bulan
            $statsStartDate = \Carbon\Carbon::create($statsYear, $statsMonth, 1)->startOfMonth()->format('Y-m-d');
            $statsEndDate = \Carbon\Carbon::create($statsYear, $statsMonth, 1)->endOfMonth()->format('Y-m-d');
        }

        // Hitung statistik per pegawai untuk periode yang dipilih (1 query, bukan N query)
        $allPeriodAttendances = \App\Models\Attendance::whereIn('worker_id', $allWorkers->pluck('id'))
            ->whereDate('attendance_date', '>=', $statsStartDate)
            ->whereDate('attendance_date', '<=', $statsEndDate)
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('worker', fn($wq) => $wq->where('department_id', $departmentId));
            })
            ->get()
            ->groupBy('worker_id');

        $workerStats = [];
        foreach ($allWorkers as $worker) {
            $attendanceItems = $allPeriodAttendances->get($worker->id, collect());

            // Hitung detail statistik
            $lateItems = $attendanceItems->where('is_late', true);
            $earlyLeaveItems = $attendanceItems->where('is_early_leave', true);
            $totalLateMinutes = $lateItems->sum('late_minutes');
            $totalEarlyLeaveMinutes = $earlyLeaveItems->sum('early_leave_minutes');
            $avgLateMinutes = $lateItems->count() > 0 ? round($totalLateMinutes / $lateItems->count()) : 0;
            $avgEarlyLeaveMinutes = $earlyLeaveItems->count() > 0 ? round($totalEarlyLeaveMinutes / $earlyLeaveItems->count()) : 0;

            $totalPresent  = $attendanceItems->whereIn('status', ['present', 'late'])->count();
            $totalLeave    = $attendanceItems->whereIn('status', ['leave', 'cuti'])->count();
            $totalSick     = $attendanceItems->where('status', 'sick')->count();
            $totalPerm     = $attendanceItems->whereIn('status', ['permission', 'izin'])->count();

            // Hitung total hari kerja dalam periode (berdasarkan jadwal shift pegawai)
            // Kemudian kurangi dengan hadir + cuti/sakit/izin → sisanya = tidak hadir (tanpa catatan)
            $totalWorkDays = $this->getWorkingDaysCount($statsStartDate, $statsEndDate, $worker);
            $totalAbsent   = max(0, $totalWorkDays - $totalPresent - $totalLeave - $totalSick - $totalPerm);

            $workerStats[$worker->id] = [
                'total_present' => $totalPresent,
                'total_late' => $lateItems->count(),
                'total_late_minutes' => $totalLateMinutes,
                'avg_late_minutes' => $avgLateMinutes,
                'total_early_leave' => $earlyLeaveItems->count(),
                'total_early_leave_minutes' => $totalEarlyLeaveMinutes,
                'avg_early_leave_minutes' => $avgEarlyLeaveMinutes,
                'total_perfect' => $attendanceItems->whereIn('status', ['present'])
                    ->where('is_late', false)
                    ->where('is_early_leave', false)
                    ->count(),
                'total_absent' => $totalAbsent,
                'total_sick' => $totalSick,
                'total_permission' => $totalPerm,
                'total_leave' => $totalLeave,
            ];
        }

        // Data filter untuk statistik
        $statsFilters = [
            'period' => $statsPeriod,
            'month' => $statsMonth,
            'year' => $statsYear,
            'date_from' => $statsDateFrom,
            'date_to' => $statsDateTo,
            'start_date' => $statsStartDate,
            'end_date' => $statsEndDate,
        ];

        // Hitung statistik keseluruhan untuk tab Riwayat — aggregate dari workerStats yang sudah benar
        // (workerStats sudah menghitung absent = hari kerja - hadir - cuti/sakit/izin)
        $allPeriodFlat = $allPeriodAttendances->flatten();
        $statsCollection = collect($workerStats);
        $historySummary = [
            'total_records' => $statsCollection->sum('total_present')
                             + $statsCollection->sum('total_absent')
                             + $statsCollection->sum('total_leave')
                             + $statsCollection->sum('total_sick')
                             + $statsCollection->sum('total_permission'),
            'present' => $statsCollection->sum('total_present'),
            'late' => $statsCollection->sum('total_late'),
            'early_leave' => $statsCollection->sum('total_early_leave'),
            'perfect' => $statsCollection->sum('total_perfect'),
            'absent' => $statsCollection->sum('total_absent'),
            'on_leave' => $statsCollection->sum('total_leave')
                        + $statsCollection->sum('total_sick')
                        + $statsCollection->sum('total_permission'),
            'period_label' => $statsPeriod === 'year'
                ? 'Tahun ' . $statsYear
                : ($statsPeriod === 'custom'
                    ? \Carbon\Carbon::parse($statsStartDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($statsEndDate)->format('d M Y')
                    : \Carbon\Carbon::create($statsYear, $statsMonth, 1)->translatedFormat('F Y')),
        ];

        // Gunakan historyFilters untuk filter form (bukan yang sudah dimodifikasi)
        return view('admin.attendance.index', compact('attendances', 'workers', 'historyFilters', 'workersWithAttendance', 'summary', 'historySummary', 'workerStats', 'statsFilters'));
    }

    public function create()
    {
        $workers = $this->getAllActiveWorkers();
        $configuredLocation = $this->getConfiguredLocation();
        $locations = collect([(object) $configuredLocation]);
        $locationsData = [
            $configuredLocation['id'] => $configuredLocation,
        ];

        return view('admin.attendance.create', compact('workers', 'locations', 'locationsData'));
    }

    /**
     * Show check-in form for specific worker
     */
    public function checkInForm(string $workerId)
    {
        try {
            $worker = $this->getWorkerById($workerId);

            if (!$worker) {
                return redirect()
                    ->route('admin.attendance.index')
                    ->with('error', 'Data pegawai tidak ditemukan');
            }

            // Cek apakah sudah check-in hari ini
            $today = now()->format('Y-m-d');
            $existingAttendance = $this->getAttendances([
                'worker_id' => $workerId,
                'date_from' => $today,
                'date_to' => $today,
            ])->first();

            if ($existingAttendance && $existingAttendance->check_in) {
                return redirect()
                    ->route('admin.attendance.show', $existingAttendance->id)
                    ->with('error', 'Pegawai ini sudah melakukan check-in hari ini');
            }

            $configuredLocation = $this->getConfiguredLocation();
            $locations = collect([(object) $configuredLocation]);
            $locationsData = [
                $configuredLocation['id'] => $configuredLocation,
            ];

            // Shift efektif hari ini (termasuk override/tukar shift jika ada)
            $shiftInfo = $worker->resolveShiftForDate($today);

            return view('admin.attendance.check-in', compact('worker', 'locations', 'locationsData', 'shiftInfo'));
        } catch (\Exception $e) {
            Log::error('Check-in form error: ' . $e->getMessage(), [
                'worker_id' => $workerId,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.attendance.index')
                ->with('error', 'Data pegawai tidak ditemukan');
        }
    }

    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|uuid|exists:workers,id',
            'admin_checkin_note' => 'required|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $configuredLocation = $this->getConfiguredLocation();
            $validated['latitude'] = (float) $configuredLocation['latitude'];
            $validated['longitude'] = (float) $configuredLocation['longitude'];

            // Add admin flag since this is from admin controller
            $validated['by_admin'] = true;
            $validated['admin_id'] = Auth::id();
            $validated['notes'] = $validated['admin_checkin_note'] ?? null;

            $worker = $this->getWorkerById((string) $validated['worker_id']);
            if ($worker) {
                $today = now()->format('Y-m-d');
                $shiftInfo = $worker->resolveShiftForDate($today);
                $shift = $shiftInfo['shift'] ?? null;
                $schedule = $shiftInfo['schedule'] ?? null;

                if ($shift && $schedule) {
                    $shiftStartDateTime = \Carbon\Carbon::parse($today . ' ' . $schedule['start_time']);
                    if (!empty($schedule['is_overnight'])) {
                        $shiftEndDateTimeToday = \Carbon\Carbon::parse($today . ' ' . $schedule['end_time']);
                        if (now()->lessThan($shiftEndDateTimeToday)) {
                            $shiftStartDateTime = $shiftStartDateTime->copy()->subDay();
                        }
                    }

                    // Admin check-in dicatat tepat 1 detik sebelum shift dimulai
                    $validated['check_in_time_override'] = $shiftStartDateTime->copy()->subSecond()->format('Y-m-d H:i:s');
                }
            }

            $attendance = $this->performCheckIn($validated);

            return redirect()
                ->route('admin.attendance.show', $attendance->id)
                ->with('success', 'Check-in berhasil dicatat oleh Admin');
        } catch (\Exception $e) {
            Log::error('Check-in error: ' . $e->getMessage(), [
                'worker_id' => $request->worker_id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function checkOutForm(string $id)
    {
        try {
            $attendance = $this->getAttendanceById($id);

            // Validasi apakah sudah check-out
            if ($attendance->check_out) {
                return redirect()
                    ->route('admin.attendance.show', $id)
                    ->with('error', 'Pegawai ini sudah melakukan check-out');
            }

            $configuredLocation = $this->getConfiguredLocation();
            $locations = collect([(object) $configuredLocation]);
            $locationsData = [
                $configuredLocation['id'] => $configuredLocation,
            ];

            $attendanceDate = $attendance->attendance_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $shiftInfo = $attendance->worker
                ? $attendance->worker->resolveShiftForDate($attendanceDate)
                : ['shift' => null, 'schedule' => null, 'source' => 'none', 'override' => null, 'swap_request' => null, 'swap_with_name' => null];

            return view('admin.attendance.check-out', compact('attendance', 'locations', 'locationsData', 'shiftInfo'));
        } catch (\Exception $e) {
            Log::error('Check-out form error: ' . $e->getMessage(), [
                'attendance_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.attendance.index')
                ->with('error', 'Data absensi tidak ditemukan');
        }
    }

    public function checkOut(Request $request, string $id)
    {
        $validated = $request->validate([
            'admin_checkout_note' => 'required|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $configuredLocation = $this->getConfiguredLocation();
            $validated['latitude'] = (float) $configuredLocation['latitude'];
            $validated['longitude'] = (float) $configuredLocation['longitude'];

            // Add admin flag since this is from admin controller
            $validated['by_admin'] = true;
            $validated['admin_id'] = Auth::id();

            $attendance = $this->performCheckOut($id, $validated);

            // Check if it was an early leave and show appropriate message
            $message = 'Check-out berhasil dicatat oleh Admin';

            if ($attendance->is_early_leave && $attendance->early_leave_minutes > 0) {
                $hours = floor($attendance->early_leave_minutes / 60);
                $minutes = $attendance->early_leave_minutes % 60;
                $earlyText = '';

                if ($hours > 0) {
                    $earlyText .= $hours . ' jam ';
                }
                if ($minutes > 0) {
                    $earlyText .= $minutes . ' menit';
                }

                $message = "Check-out berhasil dicatat oleh Admin. Perhatian: Pegawai pulang lebih awal {$earlyText} dari jadwal.";

                return redirect()
                    ->route('admin.attendance.show', $attendance->id)
                    ->with('warning', $message);
            }

            return redirect()
                ->route('admin.attendance.show', $attendance->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage(), [
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
        $attendance = $this->getAttendanceById($id);

        if (!$attendance) {
            return redirect()
                ->route('admin.attendance.index')
                ->with('error', 'Data absensi tidak ditemukan');
        }

        return view('admin.attendance.show', compact('attendance'));
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

    public function destroy(string $id)
    {
        try {
            $this->deleteAttendance($id);

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

        $attendances = $this->getAttendances($filters);

        return view('admin.attendance.report', compact('attendances', 'date'));
    }

    public function monthlyReport(Request $request)
    {
        $workerId = $request->worker_id ?? Auth::user()->worker_id;
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $report = $this->getMonthlyAttendanceReport($workerId, $month, $year);
        $workers = $this->getAllActiveWorkers();

        return view('admin.attendance.report', compact('report', 'workers', 'month', 'year'));
    }

    public function export(Request $request)
    {
        try {
            $format = $request->input('format', 'excel'); // pdf, excel, csv

            // Get department filter: prioritas dari modal, fallback ke manager restriction
            $departmentFilter = $request->input('department_id') ?: $this->getManagerDepartmentFilter();

            $filters = [
                'worker_id' => $request->input('worker_id'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'status' => $request->input('status'),
                'search' => $request->input('search'),
                'department_id' => $departmentFilter,
            ];

            // Get attendances data
            $query = \App\Models\Attendance::with(['worker.department']);

            if ($filters['worker_id']) {
                $query->where('worker_id', $filters['worker_id']);
            }
            if ($filters['date_from']) {
                $query->whereDate('attendance_date', '>=', $filters['date_from']);
            }
            if ($filters['date_to']) {
                $query->whereDate('attendance_date', '<=', $filters['date_to']);
            }
            if ($filters['status'] === 'late') {
                $query->where('is_late', true);
            } elseif ($filters['status']) {
                $query->where('status', $filters['status']);
            }
            if ($filters['department_id']) {
                $departmentId = $filters['department_id'];
                $query->whereHas('worker', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }
            if (!empty($filters['search'])) {
                $searchTerm = strtolower($filters['search']);
                $query->whereHas('worker', function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $searchTerm . '%']);
                });
            }

            $attendances = $query->orderBy('attendance_date', 'desc')->get();

            // Get worker if single worker export
            $worker = null;
            if ($filters['worker_id']) {
                $worker = $this->getWorkerById($filters['worker_id']);
            }

            $filename = 'laporan-absensi-' . now()->format('Y-m-d-His');

            // Export based on format
            switch ($format) {
                case 'pdf':
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.attendance-pdf', [
                        'attendances' => $attendances,
                        'worker' => $worker,
                        'dateFrom' => $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d F Y') : 'Semua',
                        'dateTo' => $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d F Y') : 'Semua',
                        'status' => $filters['status'],
                    ]);
                    $pdf->setPaper('a4', 'landscape');
                    return $pdf->download($filename . '.pdf');

                case 'csv':
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new AttendanceExport($filters),
                        $filename . '.csv',
                        \Maatwebsite\Excel\Excel::CSV
                    );

                default: // excel
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new AttendanceExport($filters),
                        $filename . '.xlsx'
                    );
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    // Daftar pegawai untuk absensi
    public function workerList(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'per_page' => $request->per_page ?? 15,
        ];
        $workers = $this->getWorkers($filters);
        return view('admin.attendance.worker-list', compact('workers'));
    }

    // Riwayat absensi per pegawai
    public function history(Request $request, $workerId)
    {
        $worker = $this->getWorkerById($workerId);
        if (!$worker) {
            return back()->with('error', 'Pegawai tidak ditemukan');
        }

        // Get month and year from request, default to current month
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Create date range for the month
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        // Get attendances using getAll method with proper filters
        $attendancePaginated = $this->getAttendances([
            'worker_id' => $worker->id,
            'date_from' => $startDate->format('Y-m-d'),
            'date_to' => $endDate->format('Y-m-d'),
            'per_page' => 999,
        ]);

        // Extract items from paginator - these are already loaded with relations
        $attendances = collect($attendancePaginated->items());

        // Calculate statistics
        $totalPresent = $attendances->whereIn('status', ['present', 'late'])->count();
        $totalAbsent = $attendances->where('status', 'absent')->count();
        $totalLate = $attendances->where('is_late', true)->count();
        $totalLeave = $attendances->where('status', 'leave')->count();

        $daysInMonth = $startDate->daysInMonth;

        // Get worker's active shift with relation
        $worker->load(['activeWorkerShift.shift', 'workerShifts.shift', 'department']);

        // Get all shifts for reference
        $allShifts = \App\Models\Shift::where('is_active', true)
            ->orderBy('start_time')
            ->get();

        // Create calendar array with all dates
        $calendarData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::create($year, $month, $day);
            $dateKey = $date->format('Y-m-d');

            // Find attendance for this date
            $dayAttendance = $attendances->first(function($att) use ($dateKey) {
                return $att->attendance_date &&
                       \Carbon\Carbon::parse($att->attendance_date)->format('Y-m-d') === $dateKey;
            });

            // Get shift schedule for this date
            $shift = null;
            $shiftSchedule = null;
            $shiftDateTimeRange = null;

            if (method_exists($worker, 'resolveShiftForDate')) {
                $shiftInfo = $worker->resolveShiftForDate($date);
                $shift = $shiftInfo['shift'] ?? null;
                $shiftSchedule = $shiftInfo['schedule'] ?? null;
            } elseif (method_exists($worker, 'getShiftForDate')) {
                $shiftId = $worker->getShiftForDate($date);
                $shift = $shiftId ? \App\Models\Shift::find($shiftId) : null;
                $shiftSchedule = $shift ? $shift->getScheduleForDate($date) : null;
            } elseif ($worker->activeWorkerShift && $worker->activeWorkerShift->shift) {
                // Fallback: use active shift if getShiftForDate doesn't exist
                $shift = $worker->activeWorkerShift->shift;
                $shiftSchedule = $shift->getScheduleForDate($date);
            }

            if ($shift && $shiftSchedule) {
                $shiftStart = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $shiftSchedule['start_time']);
                $shiftEnd = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $shiftSchedule['end_time']);

                if (($shiftSchedule['is_overnight'] ?? false) || $shiftEnd->lte($shiftStart)) {
                    $shiftEnd->addDay();
                }

                $shiftDateTimeRange = $shiftStart->format('Y-m-d H:i:s') . ' - ' . $shiftEnd->format('Y-m-d H:i:s');
            }

            $calendarData[] = [
                'date' => $date,
                'day' => $day,
                'dayName' => $date->translatedFormat('l'),
                'attendance' => $dayAttendance,
                'shift' => $shift,
                'shiftSchedule' => $shiftSchedule,
                'shiftDateTimeRange' => $shiftDateTimeRange,
                'isWeekend' => $date->isSunday(), // Only Sunday is weekend
            ];
        }

        return view('admin.attendance.history', compact(
            'worker',
            'calendarData',
            'month',
            'year',
            'totalPresent',
            'totalAbsent',
            'totalLate',
            'totalLeave',
            'startDate',
            'endDate',
            'allShifts'
        ));
    }

    // Export absensi pegawai (Excel)
    public function exportWorkerAttendance(Request $request, $workerId)
    {
        $worker = $this->getWorkerById($workerId);
        if (!$worker) {
            return back()->with('error', 'Pegawai tidak ditemukan');
        }
        $filters = [
            'worker_id' => $workerId,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'status' => $request->status,
        ];
        $filename = 'absensi-' . str_replace(' ', '-', strtolower($worker->name)) . '-' . now()->format('Y-m-d-His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AttendanceExport($filters), $filename);
    }

    // Export riwayat absensi pegawai per hari (PDF/Excel)
    public function exportWorkerHistory(Request $request, $workerId)
    {
        $worker = $this->getWorkerById($workerId);
        if (!$worker) {
            return back()->with('error', 'Pegawai tidak ditemukan');
        }

        $format = $request->input('format', 'pdf');
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $worker->load(['activeWorkerShift.shift', 'workerShifts.shift', 'department']);

        $rows = $this->buildWorkerAttendanceCalendarRows($worker, $startDate, $endDate);
        $rowsCollection = collect($rows);
        $summary = [
            'total_days' => $rowsCollection->count(),
            'present' => $rowsCollection->whereIn('status', ['Hadir', 'Terlambat'])->count(),
            'late' => $rowsCollection->where('status', 'Terlambat')->count(),
            'absent' => $rowsCollection->where('status', 'Tidak Hadir')->count(),
            'leave' => $rowsCollection->where('status', 'Cuti')->count(),
            'sick' => $rowsCollection->where('status', 'Sakit')->count(),
            'permission' => $rowsCollection->where('status', 'Izin')->count(),
            'no_data' => $rowsCollection->where('status', 'Belum Ada Data')->count(),
        ];
        $filename = 'riwayat-absensi-' . str_replace(' ', '-', strtolower($worker->name)) . '-' . $startDate->format('Y-m') . '-' . now()->format('His');

        switch ($format) {
            case 'excel':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\WorkerAttendanceCalendarExport($worker, $rows, $startDate, $endDate),
                    $filename . '.xlsx'
                );
            case 'pdf':
            default:
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.worker-attendance-calendar-pdf', [
                    'worker' => $worker,
                    'rows' => $rows,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'summary' => $summary,
                ]);
                $pdf->setPaper('a4', 'portrait');
                return $pdf->download($filename . '.pdf');
        }
    }

    /**
     * Menampilkan detail statistik kehadiran pegawai
     */
    public function workerStats(Request $request, $workerId)
    {
        $worker = $this->getWorkerById($workerId);
        if (!$worker) {
            return back()->with('error', 'Pegawai tidak ditemukan');
        }

        // Default filter untuk bulan ini
        $dateFrom = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->endOfMonth()->format('Y-m-d');

        $filters = [
            'worker_id' => $workerId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        // Ambil semua absensi pegawai dalam periode tersebut
        $attendances = $this->getAttendances($filters);

        // Debug: Load worker dengan relasi
        $worker->load(['shift', 'workerShifts.shift', 'department']);

        // Hitung statistik
        $stats = $this->calculateWorkerStats($attendances, $dateFrom, $dateTo, $worker);

        return view('admin.attendance.worker-stats', compact('worker', 'attendances', 'stats', 'dateFrom', 'dateTo'));
    }

    /**
     * Menghitung statistik kehadiran pegawai
     */
    private function calculateWorkerStats($attendances, $dateFrom, $dateTo, $worker = null)
    {
        // Jika worker tidak dikirim via parameter, ambil dari attendance pertama
        if (!$worker && $attendances->isNotEmpty()) {
            $worker = $attendances->first()->worker;
        }

        $totalWorkDays = $this->getWorkingDaysCount($dateFrom, $dateTo, $worker);

        $stats = [
            'total_work_days' => $totalWorkDays,
            'total_present' => 0,
            'total_absent' => 0,
            'check_in_only' => 0,
            'check_out_only' => 0,
            'complete_attendance' => 0,
            'late_arrivals' => 0,
            'early_departures' => 0,
            'leave_days' => 0,
            'sick_days' => 0,
            'permission_days' => 0,
        ];

        foreach ($attendances as $attendance) {
            // Untuk status present, artinya pegawai hadir
            if ($attendance->status === 'present') {
                $stats['total_present']++;

                // Hitung kategori kehadiran berdasarkan check_in dan check_out
                if ($attendance->check_in && $attendance->check_out) {
                    $stats['complete_attendance']++;
                } elseif ($attendance->check_in && !$attendance->check_out) {
                    $stats['check_in_only']++;
                } elseif (!$attendance->check_in && $attendance->check_out) {
                    $stats['check_out_only']++;
                }

                // Cek keterlambatan
                if ($attendance->is_late) {
                    $stats['late_arrivals']++;
                }

                // Cek pulang lebih awal
                if ($attendance->is_early_leave) {
                    $stats['early_departures']++;
                }
            }

            // Hitung jenis cuti/izin
            switch ($attendance->status) {
                case 'leave':
                case 'cuti':
                    $stats['leave_days']++;
                    break;
                case 'sick':
                case 'sakit':
                    $stats['sick_days']++;
                    break;
                case 'permission':
                case 'izin':
                    $stats['permission_days']++;
                    break;
            }
        }

        // Hitung total absent (hari kerja - hadir - cuti/sakit/izin)
        $stats['total_absent'] = max(0, $totalWorkDays - $stats['total_present'] - $stats['leave_days'] - $stats['sick_days'] - $stats['permission_days']);

        // Hitung persentase
        $stats['attendance_percentage'] = $totalWorkDays > 0 ? round(($stats['total_present'] / $totalWorkDays) * 100, 1) : 0;
        $stats['absence_percentage'] = $totalWorkDays > 0 ? round(($stats['total_absent'] / $totalWorkDays) * 100, 1) : 0;

        return $stats;
    }

    private function buildWorkerAttendanceCalendarRows($worker, $startDate, $endDate): array
    {
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

            $leaveRequest = $leaveRequests->first(function ($leave) use ($date) {
                return $date->between($leave->start_date, $leave->end_date);
            });

            $shift = null;
            if (method_exists($worker, 'getShiftForDate')) {
                $shiftId = $worker->getShiftForDate($date);
                $shift = $shiftId ? \App\Models\Shift::find($shiftId) : null;
            } elseif ($worker->activeWorkerShift && $worker->activeWorkerShift->shift) {
                $shift = $worker->activeWorkerShift->shift;
            }

            $statusLabel = 'Tidak Hadir';
            $notes = '-';
            $checkIn = '-';
            $checkOut = '-';
            $lateInfo = '-';

            if ($attendance) {
                $statusLabel = match ($attendance->status) {
                    'present' => 'Hadir',
                    'late' => 'Terlambat',
                    'absent' => 'Tidak Hadir',
                    'leave' => 'Cuti',
                    'sick' => 'Sakit',
                    'permission' => 'Izin',
                    default => ucfirst($attendance->status),
                };
                $checkIn = $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-';
                $checkOut = $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-';
                $lateInfo = $attendance->is_late ? ($attendance->late_minutes . ' menit') : '-';
                $notes = $attendance->notes ?? '-';
            } elseif ($leaveRequest) {
                $leaveName = $leaveRequest->leaveType->name;
                $leaveNameLower = strtolower($leaveName);
                if (str_contains($leaveNameLower, 'sakit')) {
                    $statusLabel = 'Sakit';
                } elseif (str_contains($leaveNameLower, 'izin')) {
                    $statusLabel = 'Izin';
                } else {
                    $statusLabel = 'Cuti';
                }
                $notes = $leaveName;
            } elseif ($isFutureDate) {
                $statusLabel = 'Belum Ada Data';
                $notes = 'Tanggal belum terlewati';
            } else {
                $isHolidayOff = $isHoliday && !$requiresHolidayAttendance;
                $isWorkday = !empty($shift) && !$isOffDay && !$isHolidayOff;

                if (!$isWorkday) {
                    $statusLabel = 'Libur';

                    if ($isOffDay) {
                        $notes = 'Libur Off-day';
                    } elseif ($isHolidayOff) {
                        $notes = 'Libur Nasional: ' . ($holiday->name ?? '-');
                    } elseif (empty($shift)) {
                        $notes = 'Libur (Tidak ada jadwal shift)';
                    }
                } elseif ($isHoliday && $requiresHolidayAttendance) {
                    $notes = 'Libur Nasional (Tetap bertugas): ' . ($holiday->name ?? '-');
                }
            }

            $rows[] = [
                'date' => $date->format('d/m/Y'),
                'day_name' => $date->translatedFormat('l'),
                'shift_name' => $shift ? $shift->name : '-',
                'shift_time' => $shift
                    ? (\Carbon\Carbon::parse($shift->getScheduleForDate($date)['start_time'])->format('H:i') . ' - ' . \Carbon\Carbon::parse($shift->getScheduleForDate($date)['end_time'])->format('H:i'))
                    : '-',
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => $statusLabel,
                'late' => $lateInfo,
                'notes' => $notes,
            ];
        }

        return $rows;
    }

    /**
     * Menghitung jumlah hari kerja berdasarkan jadwal shift pegawai dalam periode tertentu
     */
    /**
     * Hitung jumlah hari kerja YANG SUDAH LEWAT dalam periode.
     * Hanya menghitung hari yang sudah lewat (sebelum hari ini), karena hari ini belum selesai.
     * Mengecualikan: hari libur nasional (kecuali dept standby), off-day shift.
     * TIDAK mengecualikan cuti/dinas — itu ditangani formula penghitung di caller.
     */
    private function getWorkingDaysCount($dateFrom, $dateTo, $worker = null)
    {
        $start = \Carbon\Carbon::parse($dateFrom);
        $end   = \Carbon\Carbon::parse($dateTo);
        $today = now()->startOfDay();

        // Cap end di kemarin — hari ini belum lewat, belum bisa dihitung absent
        if ($end->gte($today)) {
            $end = $today->copy()->subDay();
        }

        if ($start->gt($end)) {
            return 0;
        }

        // Ambil shift days dari worker -----------
        $workingDays = [1, 2, 3, 4, 5, 6]; // Default: Senin-Sabtu (rumah sakit)

        $activeShift = null;
        if ($worker) {
            if ($worker->workerShifts) {
                $activeWorkerShift = $worker->workerShifts
                    ->where('is_active', true)
                    ->where('effective_from', '<=', $start->format('Y-m-d'))
                    ->filter(fn($ws) => is_null($ws->effective_until) || $ws->effective_until >= $end->format('Y-m-d'))
                    ->first();

                if ($activeWorkerShift && $activeWorkerShift->shift) {
                    $activeShift = $activeWorkerShift->shift;
                }
            }
            if (!$activeShift && $worker->shift) {
                $activeShift = $worker->shift;
            }

            if ($activeShift) {
                $workingDays = [];
                if (isset($activeShift->working_days)) {
                    $workingDays = array_map('intval', explode(',', $activeShift->working_days));
                } else {
                    $dayFlags = [
                        'is_sunday'    => 0, 'is_monday'   => 1, 'is_tuesday'  => 2,
                        'is_wednesday' => 3, 'is_thursday' => 4, 'is_friday'   => 5,
                        'is_saturday'  => 6,
                    ];
                    foreach ($dayFlags as $flag => $num) {
                        if (isset($activeShift->$flag) && $activeShift->$flag) {
                            $workingDays[] = $num;
                        }
                    }
                    if (empty($workingDays)) {
                        $workingDays = [1, 2, 3, 4, 5, 6];
                    }
                }
            }
        }

        // Ambil libur nasional dalam periode
        $holidays = \App\Models\Holiday::where('is_national', true)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->keyBy(fn($h) => \Carbon\Carbon::parse($h->date)->format('Y-m-d'));

        $requiresHolidayAttendance = false;
        if ($worker && $worker->department) {
            $requiresHolidayAttendance = (bool) ($worker->department->requires_holiday_attendance ?? false);
        }

        // Off-days (pola libur pekerja)
        $offDays = [];
        if ($worker) {
            $offDayService = app(\App\Services\WorkerOffDay\WorkerOffDayService::class);
            if (method_exists($offDayService, 'getOffDaysInRange')) {
                $offDays = array_flip($offDayService->getOffDaysInRange($worker, $start, $end));
            }
        }

        // Iterate per hari, hitung yang benar-benar hari kerja efektif
        $workDays = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');

            // Bukan hari kerja menurut jadwal shift
            if (!in_array($date->dayOfWeek, $workingDays)) {
                continue;
            }

            // Libur nasional (kecuali dept standby)
            if (!$requiresHolidayAttendance && isset($holidays[$dateKey])) {
                continue;
            }

            // Off-day pekerja
            if (isset($offDays[$dateKey])) {
                continue;
            }

            $workDays++;
        }

        return $workDays;
    }

    /**
     * Export statistik kehadiran pegawai ke PDF
     */
    public function exportStatsPdf(Request $request, $workerId)
    {
        $worker = $this->getWorkerById($workerId);
        if (!$worker) {
            return back()->with('error', 'Pegawai tidak ditemukan');
        }

        $dateFrom = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->endOfMonth()->format('Y-m-d');

        $filters = [
            'worker_id' => $workerId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $attendances = $this->getAttendances($filters);
        $worker->load(['shift', 'workerShifts.shift', 'department']);
        $stats = $this->calculateWorkerStats($attendances, $dateFrom, $dateTo, $worker);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.attendance.exports.stats-pdf', compact('worker', 'attendances', 'stats', 'dateFrom', 'dateTo'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'statistik-kehadiran-' . str_replace(' ', '-', strtolower($worker->name)) . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export statistik kehadiran pegawai ke Excel
     */
    public function exportStatsExcel(Request $request, $workerId)
    {
        $worker = $this->getWorkerById($workerId);
        if (!$worker) {
            return back()->with('error', 'Pegawai tidak ditemukan');
        }

        $dateFrom = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->endOfMonth()->format('Y-m-d');

        $filters = [
            'worker_id' => $workerId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $attendances = $this->getAttendances($filters);
        $worker->load(['shift', 'workerShifts.shift', 'department']);
        $stats = $this->calculateWorkerStats($attendances, $dateFrom, $dateTo, $worker);

        $filename = 'statistik-kehadiran-' . str_replace(' ', '-', strtolower($worker->name)) . '-' . now()->format('Y-m-d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceStatsExport($worker, $attendances, $stats, $dateFrom, $dateTo),
            $filename
        );
    }

    /**
     * Export Absensi Hari Ini (Today's Attendance)
     */
    public function exportTodayAttendance(Request $request)
    {
        try {
            $format = $request->input('format', 'excel'); // pdf, excel
            $selectedDate = $request->attendance_date ?? now()->format('Y-m-d');

            // Get all active workers with relationships
            $workers = $this->getAllActiveWorkers();
            $workers->load(['shift', 'department', 'workerShifts.shift']);

            // Get today's attendances dengan relationship - FIXED: gunakan attendance_date
            $attendances = \App\Models\Attendance::with(['worker.department'])
                ->whereDate('attendance_date', $selectedDate)
                ->get();

            // Pre-load semua leave requests (1 query, bukan N query)
            $leaveRequestsByWorker = \App\Models\LeaveRequest::whereIn('worker_id', $workers->pluck('id'))
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $selectedDate)
                ->whereDate('end_date', '>=', $selectedDate)
                ->with('leaveType')
                ->get()
                ->keyBy('worker_id');

            // Map attendance data to workers
            $workersWithAttendance = $workers->map(function ($worker) use ($attendances, $selectedDate, $leaveRequestsByWorker) {
                $todayAttendance = $attendances->where('worker_id', $worker->id)->first();

                // Ambil leave request dari pre-loaded collection (tanpa query tambahan)
                $leaveRequest = $leaveRequestsByWorker->get($worker->id);

                $worker->today_attendance = $todayAttendance;
                $worker->leave_request = $leaveRequest;
                $worker->check_in_time = $todayAttendance && $todayAttendance->check_in ?
                    \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i:s') : null;
                $worker->check_out_time = $todayAttendance && $todayAttendance->check_out ?
                    \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i:s') : null;
                $worker->is_late = $todayAttendance ? $todayAttendance->is_late : false;
                $worker->late_minutes = $todayAttendance ? ($todayAttendance->late_minutes ?? 0) : 0;

                $isOffDay = method_exists($worker, 'isOffDay')
                    ? $worker->isOffDay(\Carbon\Carbon::parse($selectedDate))
                    : false;
                $worker->is_off_day = $isOffDay;

                // Determine attendance status - prioritaskan leave request
                if ($leaveRequest) {
                    $leaveTypeName = $leaveRequest->leaveType->name;
                    // Deteksi tipe leave berdasarkan nama
                    if (str_contains(strtolower($leaveTypeName), 'sakit')) {
                        $worker->attendance_status = 'sick';
                        $worker->status_label = 'Sakit';
                    } elseif (str_contains(strtolower($leaveTypeName), 'izin')) {
                        $worker->attendance_status = 'permission';
                        $worker->status_label = 'Izin';
                    } else {
                        $worker->attendance_status = 'leave';
                        $worker->status_label = $leaveTypeName;
                    }
                } elseif (!$todayAttendance && $isOffDay) {
                    $worker->attendance_status = 'off_day';
                    $worker->status_label = 'Libur Kerja';
                } elseif (!$todayAttendance) {
                    $worker->attendance_status = 'not_checked_in';
                    $worker->status_label = 'Belum Absen';
                } elseif ($todayAttendance->status === 'leave') {
                    $worker->attendance_status = 'leave';
                    $worker->status_label = 'Cuti';
                } elseif ($todayAttendance->status === 'sick') {
                    $worker->attendance_status = 'sick';
                    $worker->status_label = 'Sakit';
                } elseif ($todayAttendance->status === 'permission') {
                    $worker->attendance_status = 'permission';
                    $worker->status_label = 'Izin';
                } elseif ($todayAttendance->is_late) {
                    $worker->attendance_status = 'late';
                    $worker->status_label = 'Terlambat';
                } else {
                    $worker->attendance_status = 'present';
                    $worker->status_label = 'Hadir';
                }

                return $worker;
            });

            // Calculate statistics
            $stats = [
                'total_workers' => $workersWithAttendance->count(),
                'present' => $workersWithAttendance->whereIn('attendance_status', ['present', 'late'])->count(),
                'late' => $workersWithAttendance->where('attendance_status', 'late')->count(),
                'not_checked_in' => $workersWithAttendance->where('attendance_status', 'not_checked_in')->count(),
                'off_day' => $workersWithAttendance->where('attendance_status', 'off_day')->count(),
                'leave' => $workersWithAttendance->where('attendance_status', 'leave')->count(),
                'sick' => $workersWithAttendance->where('attendance_status', 'sick')->count(),
                'permission' => $workersWithAttendance->where('attendance_status', 'permission')->count(),
                'on_leave_total' => $workersWithAttendance->whereIn('attendance_status', ['leave', 'sick', 'permission'])->count(),
            ];

            $filename = 'absensi-' . \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');
            $dateFormatted = \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y');

            // Export based on format
            switch ($format) {
                case 'pdf':
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.today-attendance-pdf', [
                        'workers' => $workersWithAttendance,
                        'stats' => $stats,
                        'date' => $dateFormatted,
                        'dateRaw' => $selectedDate,
                    ]);
                    $pdf->setPaper('a4', 'portrait'); // Changed to portrait
                    return $pdf->download($filename . '.pdf');

                case 'excel':
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\TodayAttendanceExport($workersWithAttendance, $stats, $selectedDate),
                        $filename . '.xlsx'
                    );

                default:
                    return back()->with('error', 'Format tidak didukung');
            }
        } catch (\Exception $e) {
            Log::error('Export today attendance error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    private function getAllActiveWorkers()
    {
        return Worker::where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function getWorkersByDepartment(string $departmentId)
    {
        return Worker::where('status', 'active')
            ->where('department_id', $departmentId)
            ->orderBy('name')
            ->get();
    }

    private function getWorkerById(string $workerId): ?Worker
    {
        return Worker::find($workerId);
    }

    private function getWorkers(array $filters)
    {
        $query = Worker::query()->with(['department']);

        if (!empty($filters['search'])) {
            $search = strtolower((string) $filters['search']);
            $query->where(function ($sub) use ($search) {
                $sub->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $search . '%']);
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        return $query->orderBy('name')->paginate($perPage);
    }

    private function getAttendances(array $filters = [])
    {
        $query = Attendance::with([
            'worker.department',
            'shift',
        ]);

        if (!empty($filters['attendance_date'])) {
            $query->whereDate('attendance_date', $filters['attendance_date']);
        }

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

        if (!empty($filters['department_id'])) {
            $query->whereHas('worker', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if (!empty($filters['search'])) {
            $search = strtolower((string) $filters['search']);
            $query->whereHas('worker', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $search . '%']);
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        return $query->orderByDesc('attendance_date')->orderByDesc('check_in')->paginate($perPage);
    }

    private function getAttendanceById(string $id): ?Attendance
    {
        return Attendance::with([
            'worker.department',
            'shift',
            'photos',
            'checkInAdmin',
            'checkOutAdmin',
        ])->find($id);
    }

    private function getMonthlyAttendanceReport(string $workerId, int $month, int $year)
    {
        return Attendance::with(['shift'])
            ->where('worker_id', $workerId)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->get();
    }

    private function performCheckIn(array $data): Attendance
    {
        DB::beginTransaction();

        try {
            $workerId = (string) $data['worker_id'];
            $today = now()->format('Y-m-d');

            $worker = Worker::with(['department'])->find($workerId);
            if (!$worker) {
                throw new \Exception('Data pekerja tidak ditemukan.');
            }

            $offDayService = app(\App\Services\WorkerOffDay\WorkerOffDayService::class);
            $offDayCheck = $offDayService->canPerformAttendance($worker, $today, 'check_in');
            if (!($offDayCheck['can_perform'] ?? false)) {
                throw new \Exception($offDayCheck['message'] ?? 'Hari ini termasuk hari libur Anda.');
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
            $checkInTime = !empty($data['check_in_time_override'])
                ? \Carbon\Carbon::parse($data['check_in_time_override'])
                : now();

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

            // Jika check-in oleh admin, skip validasi jarak (set distance ke 0)
            $isAdminCheckIn = (bool) ($data['by_admin'] ?? false);
            if ($isAdminCheckIn) {
                $distance = 0; // Admin check-in tidak perlu validasi jarak
            } else {
                // Validasi jarak hanya untuk check-in normal
                if ($status === 'present' && $distance > (float) $configuredLocation['radius']) {
                    throw new \Exception('Anda berada di luar radius lokasi absensi. Silakan mendekat ke lokasi yang ditentukan.');
                }
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
                'check_in_by_admin' => (bool) ($data['by_admin'] ?? false),
                'check_in_admin_id' => ($data['by_admin'] ?? false) ? ($data['admin_id'] ?? null) : null,
                'status' => $status,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'notes' => $this->buildAttendanceNotes($data, false),
            ]);

            if (isset($data['photo']) && $data['photo']) {
                $photoPath = $this->savePhoto($data['photo'], 'check_in', $workerId);

                AttendancePhoto::create([
                    'attendance_id' => $attendance->id,
                    'photo_path' => $photoPath,
                    'photo_type' => 'check_in',
                    'taken_at' => $checkInTime,
                ]);
            }

            DB::commit();

            return $attendance->fresh(['worker.department', 'shift', 'photos']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function performCheckOut(string $attendanceId, array $data): Attendance
    {
        DB::beginTransaction();

        try {
            $attendance = Attendance::with(['worker.department'])->find($attendanceId);
            if (!$attendance) {
                throw new \Exception('Data absensi tidak ditemukan.');
            }

            $worker = $attendance->worker;
            if (!$worker) {
                throw new \Exception('Data pekerja tidak ditemukan.');
            }

            $offDayService = app(\App\Services\WorkerOffDay\WorkerOffDayService::class);
            $offDayCheck = $offDayService->canPerformAttendance(
                $worker,
                now()->format('Y-m-d'),
                'check_out',
                $attendance->attendance_date?->format('Y-m-d')
            );
            if (!($offDayCheck['can_perform'] ?? false)) {
                throw new \Exception($offDayCheck['message'] ?? 'Tidak dapat check-out di hari libur.');
            }

            if ($attendance->check_out) {
                throw new \Exception('Anda sudah melakukan check-out.');
            }

            if (!$attendance->check_in) {
                throw new \Exception('Anda belum melakukan check-in. Tidak dapat melakukan check-out.');
            }

            $shift = $attendance->shift;
            if (!$shift) {
                throw new \Exception('Jadwal shift tidak ditemukan.');
            }

            $schedule = $shift->getScheduleForDate($attendance->attendance_date);
            $checkInDateTime = \Carbon\Carbon::parse($attendance->check_in);
            $shiftBaseDate = $checkInDateTime->copy()->startOfDay();
            $shiftStartDateTime = \Carbon\Carbon::parse($shiftBaseDate->format('Y-m-d') . ' ' . $schedule['start_time']);
            $shiftEndDateTime = \Carbon\Carbon::parse($shiftBaseDate->format('Y-m-d') . ' ' . $schedule['end_time']);

            if (!empty($schedule['is_overnight']) && $shiftEndDateTime->lessThanOrEqualTo($shiftStartDateTime)) {
                $shiftEndDateTime->addDay();
            }

            $checkOutWindowAfterMinutes = (int) round((float) config('attendance.check_out_window_after_hours', 1.5) * 60);
            $maxCheckoutTime = $shiftEndDateTime->copy()->addMinutes($checkOutWindowAfterMinutes);
            $isAdminCheckout = (bool) ($data['by_admin'] ?? false);

            // Checkout admin dipatok ke 1 detik setelah shift berakhir.
            // Untuk non-admin tetap menggunakan waktu saat proses checkout.
            $checkOutTime = !empty($data['check_out_time_override'])
                ? \Carbon\Carbon::parse($data['check_out_time_override'])
                : ($isAdminCheckout ? $shiftEndDateTime->copy()->addSecond() : now());

            // Admin dapat melakukan checkout kapan saja
            if (!$isAdminCheckout && $checkOutTime->greaterThan($maxCheckoutTime)) {
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

            // Jika check-out oleh admin, skip validasi jarak (set distance ke 0)
            if ($isAdminCheckout) {
                $distance = 0; // Admin check-out tidak perlu validasi jarak
            } else {
                // Validasi jarak hanya untuk check-out normal
                if ($attendance->status === 'present' && $distance > (float) $configuredLocation['radius']) {
                    throw new \Exception('Anda berada di luar radius lokasi absensi. Silakan mendekat ke lokasi yang ditentukan.');
                }
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

            if ($isAdminCheckout) {
                $adminName = \App\Models\User::find($data['admin_id'] ?? null)?->name ?? 'Admin';
                $adminNote = trim((string) ($data['admin_checkout_note'] ?? ''));
                $adminAudit = "[ADMIN] Check-out dicatat oleh {$adminName}";
                if ($adminNote !== '') {
                    $adminAudit .= ". Keterangan: {$adminNote}";
                }
                $noteLines[] = $adminAudit;
            }

            $combinedNotes = trim(implode("\n", array_filter(array_merge([$existingNotes], $noteLines))));

            $attendance->update([
                'check_out' => $checkOutTime->format('Y-m-d H:i:s'),
                'distance_check_out' => $distance,
                'check_out_by_admin' => $isAdminCheckout,
                'check_out_admin_id' => $isAdminCheckout ? ($data['admin_id'] ?? null) : null,
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
                ]);
            }

            DB::commit();

            return $attendance->fresh(['worker.department', 'shift', 'photos']);
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

    private function deleteAttendance(string $id): bool
    {
        $attendance = Attendance::with(['photos'])->find($id);

        if (!$attendance) {
            throw new \Exception('Data absensi tidak ditemukan.');
        }

        foreach ($attendance->photos as $photo) {
            if (!empty($photo->photo_path) && Storage::exists($photo->photo_path)) {
                Storage::delete($photo->photo_path);
            }
            $photo->delete();
        }

        return (bool) $attendance->delete();
    }

    /**
     * Get attendance detail for API
     */
    public function getAttendanceDetail($id)
    {
        try {
            $attendance = $this->getAttendanceById($id);

            if (!$attendance) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            // Load relationships
            $attendance->load(['worker']);

            return response()->json([
                'id' => $attendance->id,
                'status' => $attendance->status,
                'check_in_time' => $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : null,
                'check_out_time' => $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : null,
                'is_late' => $attendance->is_late,
                'late_minutes' => $attendance->late_minutes ?? 0,
                'is_early_leave' => $attendance->is_early_leave ?? false,
                'early_leave_minutes' => $attendance->early_leave_minutes ?? 0,
                'location' => config('attendance.location.name', null),
                'notes' => $attendance->notes,
                'worker' => [
                    'name' => $attendance->worker->name,
                    'nip' => $attendance->worker->nip,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Build attendance notes untuk check-in/check-out
     * Combine admin notes dengan existing notes
     */
    private function buildAttendanceNotes(array $data, bool $isCheckOut = false): ?string
    {
        $noteLines = [];

        // Existing notes dari normal flow
        if (!empty($data['notes'])) {
            $noteLines[] = $data['notes'];
        }

        // Admin check-in note
        if (!$isCheckOut && !empty($data['admin_checkin_note'])) {
            $adminName = \App\Models\User::find($data['admin_id'] ?? null)?->name ?? 'Admin';
            $adminAudit = "[ADMIN] Check-in dicatat oleh {$adminName}";
            $adminNote = trim((string) $data['admin_checkin_note']);
            if ($adminNote !== '') {
                $adminAudit .= ". Keterangan: {$adminNote}";
            }
            $noteLines[] = $adminAudit;
        }

        // Admin check-out note (handled separately in performCheckOut)
        if ($isCheckOut && !empty($data['admin_checkout_note'])) {
            $adminName = \App\Models\User::find($data['admin_id'] ?? null)?->name ?? 'Admin';
            $adminAudit = "[ADMIN] Check-out dicatat oleh {$adminName}";
            $adminNote = trim((string) $data['admin_checkout_note']);
            if ($adminNote !== '') {
                $adminAudit .= ". Keterangan: {$adminNote}";
            }
            $noteLines[] = $adminAudit;
        }

        if (empty($noteLines)) {
            return null;
        }

        return trim(implode("\n", $noteLines));
    }

}

