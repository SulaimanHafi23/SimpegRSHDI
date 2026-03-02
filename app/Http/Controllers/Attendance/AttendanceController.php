<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Traits\DepartmentFilterable;
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
    use DepartmentFilterable;
    public function __construct(
        protected AttendanceService $attendanceService,
        protected WorkerService $workerService,
        protected LocationService $locationService
    ) {
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
        $attendances = $this->attendanceService->getAll($historyFilters);

        // Get workers from user's department if Manager
        if ($departmentId) {
            $workers = $this->workerService->getByDepartment($departmentId);
            $allWorkers = $this->workerService->getByDepartment($departmentId);
        } else {
            $workers = $this->workerService->getAllActive();
            $allWorkers = $this->workerService->getAllActive();
        }

        $locations = $this->locationService->getAllActive();

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
        $todayAttendances = $this->attendanceService->getAll([
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
        return view('admin.attendance.index', compact('attendances', 'workers', 'historyFilters', 'workersWithAttendance', 'locations', 'summary', 'historySummary', 'workerStats', 'statsFilters'));
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

    /**
     * Show check-in form for specific worker
     */
    public function checkInForm(string $workerId)
    {
        try {
            $worker = $this->workerService->getById($workerId);

            if (!$worker) {
                return redirect()
                    ->route('admin.attendance.index')
                    ->with('error', 'Data pegawai tidak ditemukan');
            }

            // Cek apakah sudah check-in hari ini
            $today = now()->format('Y-m-d');
            $existingAttendance = $this->attendanceService->getAll([
                'worker_id' => $workerId,
                'date_from' => $today,
                'date_to' => $today,
            ])->first();

            if ($existingAttendance && $existingAttendance->check_in) {
                return redirect()
                    ->route('admin.attendance.show', $existingAttendance->id)
                    ->with('error', 'Pegawai ini sudah melakukan check-in hari ini');
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

            // Get worker's current shift
            $currentShift = $worker->getCurrentShift();

            return view('admin.attendance.check-in', compact('worker', 'locations', 'locationsData', 'currentShift'));
        } catch (\Exception $e) {
            \Log::error('Check-in form error: ' . $e->getMessage(), [
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
            'location_id' => 'required|uuid|exists:locations,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            // Add admin flag since this is from admin controller
            $validated['by_admin'] = true;
            $validated['admin_id'] = auth()->id();

            $attendance = $this->attendanceService->checkIn($validated);

            return redirect()
                ->route('admin.attendance.show', $attendance->id)
                ->with('success', 'Check-in berhasil dicatat oleh Admin');
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

    public function checkOutForm(string $id)
    {
        try {
            $attendance = $this->attendanceService->getById($id);

            // Validasi apakah sudah check-out
            if ($attendance->check_out) {
                return redirect()
                    ->route('admin.attendance.show', $id)
                    ->with('error', 'Pegawai ini sudah melakukan check-out');
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

            return view('admin.attendance.check-out', compact('attendance', 'locations', 'locationsData'));
        } catch (\Exception $e) {
            \Log::error('Check-out form error: ' . $e->getMessage(), [
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
            'location_id' => 'required|uuid|exists:locations,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            // Add admin flag since this is from admin controller
            $validated['by_admin'] = true;
            $validated['admin_id'] = auth()->id();

            $attendance = $this->attendanceService->checkOut($id, $validated);

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
            $format = $request->input('format', 'excel'); // pdf, excel, csv

            $filters = [
                'worker_id' => $request->input('worker_id'),
                'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
                'date_to' => $request->input('date_to', now()->endOfMonth()->format('Y-m-d')),
                'status' => $request->input('status'),
            ];

            // Get attendances data
            $query = \App\Models\Attendance::with(['worker.department', 'location']);

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

            $attendances = $query->orderBy('attendance_date', 'desc')->get();

            // Get worker if single worker export
            $worker = null;
            if ($filters['worker_id']) {
                $worker = $this->workerService->getById($filters['worker_id']);
            }

            $filename = 'laporan-absensi-' . now()->format('Y-m-d-His');

            // Export based on format
            switch ($format) {
                case 'pdf':
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.attendance-pdf', [
                        'attendances' => $attendances,
                        'worker' => $worker,
                        'dateFrom' => \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d F Y'),
                        'dateTo' => \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d F Y'),
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
        $workers = $this->workerService->getAll($filters);
        return view('admin.attendance.worker-list', compact('workers'));
    }

    // Riwayat absensi per pegawai
    public function history(Request $request, $workerId)
    {
        $worker = $this->workerService->getById($workerId);
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
        $attendancePaginated = $this->attendanceService->getAll([
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
            if (method_exists($worker, 'getShiftForDate')) {
                $shiftId = $worker->getShiftForDate($date);
                $shift = $shiftId ? \App\Models\Shift::find($shiftId) : null;
            } elseif ($worker->activeWorkerShift && $worker->activeWorkerShift->shift) {
                // Fallback: use active shift if getShiftForDate doesn't exist
                $shift = $worker->activeWorkerShift->shift;
            }

            $calendarData[] = [
                'date' => $date,
                'day' => $day,
                'dayName' => $date->translatedFormat('l'),
                'attendance' => $dayAttendance,
                'shift' => $shift,
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
        $worker = $this->workerService->getById($workerId);
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
        $worker = $this->workerService->getById($workerId);
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
        $worker = $this->workerService->getById($workerId);
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
        $attendances = $this->attendanceService->getAll($filters);

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
            'overtime_hours' => 0,
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

                // Hitung overtime jika ada (dalam menit, konversi ke jam)
                if ($attendance->overtime_minutes && $attendance->overtime_minutes > 0) {
                    $stats['overtime_hours'] += round($attendance->overtime_minutes / 60, 1);
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
        $attendances = \App\Models\Attendance::with(['location', 'shift'])
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

        $rows = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $attendance = $attendanceByDate->get($dateKey);

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
    private function getWorkingDaysCount($dateFrom, $dateTo, $worker = null)
    {
        $start = \Carbon\Carbon::parse($dateFrom);
        $end = \Carbon\Carbon::parse($dateTo);
        $workDays = 0;

        // Jika ada data worker, coba ambil jadwal shift-nya
        $workingDays = [1, 2, 3, 4, 5, 6]; // Default: Senin-Sabtu (karena rumah sakit biasanya 6 hari kerja)

        if ($worker) {
            // Cari shift aktif worker
            $activeShift = null;

            // Coba ambil dari worker shifts yang aktif
            if ($worker->workerShifts) {
                $activeWorkerShift = $worker->workerShifts
                    ->where('is_active', true)
                    ->where('effective_from', '<=', $start->format('Y-m-d'))
                    ->filter(function($ws) use ($end) {
                        return is_null($ws->effective_until) || $ws->effective_until >= $end->format('Y-m-d');
                    })
                    ->first();

                if ($activeWorkerShift && $activeWorkerShift->shift) {
                    $activeShift = $activeWorkerShift->shift;
                }
            }

            // Fallback ke shift default worker
            if (!$activeShift && $worker->shift) {
                $activeShift = $worker->shift;
            }

            // Jika ada shift, ambil hari kerja dari shift
            if ($activeShift) {
                $workingDays = [];

                // Mapping hari dalam shift (asumsi ada field seperti working_days atau individual day flags)
                // Jika tidak ada, gunakan default 5 hari kerja
                if (isset($activeShift->working_days)) {
                    // Jika ada field working_days (format: "1,2,3,4,5" untuk Senin-Jumat)
                    $workingDays = explode(',', $activeShift->working_days);
                    $workingDays = array_map('intval', $workingDays);
                } else {
                    // Cek individual day flags jika ada
                    $dayFlags = [
                        'is_sunday' => 0,
                        'is_monday' => 1,
                        'is_tuesday' => 2,
                        'is_wednesday' => 3,
                        'is_thursday' => 4,
                        'is_friday' => 5,
                        'is_saturday' => 6
                    ];

                    foreach ($dayFlags as $flag => $dayNumber) {
                        if (isset($activeShift->$flag) && $activeShift->$flag) {
                            $workingDays[] = $dayNumber;
                        }
                    }

                    // Jika tidak ada flag hari, gunakan default Senin-Sabtu untuk rumah sakit
                    if (empty($workingDays)) {
                        $workingDays = [1, 2, 3, 4, 5, 6];
                    }
                }
            }
        }

        // Hitung hari kerja berdasarkan jadwal
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (in_array($date->dayOfWeek, $workingDays)) {
                $workDays++;
            }
        }

        return $workDays;
    }

    /**
     * Export statistik kehadiran pegawai ke PDF
     */
    public function exportStatsPdf(Request $request, $workerId)
    {
        $worker = $this->workerService->getById($workerId);
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

        $attendances = $this->attendanceService->getAll($filters);
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
        $worker = $this->workerService->getById($workerId);
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

        $attendances = $this->attendanceService->getAll($filters);
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
            $workers = $this->workerService->getAllActive();
            $workers->load(['shift', 'department', 'workerShifts.shift']);

            // Get today's attendances dengan relationship - FIXED: gunakan attendance_date
            $attendances = \App\Models\Attendance::with(['worker.department', 'location'])
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
            \Log::error('Export today attendance error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    /**
     * Get attendance detail for API
     */
    public function getAttendanceDetail($id)
    {
        try {
            $attendance = $this->attendanceService->getById($id);

            if (!$attendance) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            // Load relationships
            $attendance->load(['worker', 'location']);

            return response()->json([
                'id' => $attendance->id,
                'status' => $attendance->status,
                'check_in_time' => $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : null,
                'check_out_time' => $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : null,
                'is_late' => $attendance->is_late,
                'late_minutes' => $attendance->late_minutes ?? 0,
                'is_early_leave' => $attendance->is_early_leave ?? false,
                'early_leave_minutes' => $attendance->early_leave_minutes ?? 0,
                'location' => $attendance->location ? $attendance->location->name : null,
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
}
