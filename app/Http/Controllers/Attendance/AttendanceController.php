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
    ) {
        $this->middleware(['auth']);
        $this->middleware('permission:attendance.manage');
    }

    public function index(Request $request)
    {
        // Filter untuk riwayat absensi (tanpa default tanggal)
        $historyFilters = [
            'search' => $request->search,
            'status' => $request->status,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'worker_id' => $request->worker_id,
            'per_page' => $request->per_page ?? 15,
        ];

        // Ambil data riwayat absensi berdasarkan filter
        $attendances = $this->attendanceService->getAll($historyFilters);
        $workers = $this->workerService->getAllActive();
        $locations = $this->locationService->getAllActive();

        // Ambil semua pegawai aktif untuk menampilkan yang belum absen
        $allWorkers = $this->workerService->getAllActive();
        
        // Load relationships yang diperlukan
        $allWorkers->load(['shift', 'workerShifts.shift', 'department']);
        
        // Ambil data absensi hari ini untuk semua pegawai
        $todayAttendances = $this->attendanceService->getAll([
            'date_from' => now()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
            'per_page' => 1000, // Ambil semua data hari ini
        ]);

        // Buat array workers dengan status absensi hari ini
        $workersWithAttendance = $allWorkers->map(function ($worker) use ($todayAttendances) {
            $todayAttendance = $todayAttendances->firstWhere('worker_id', $worker->id);
            
            $worker->today_attendance = $todayAttendance;
            $worker->attendance_status = $todayAttendance ? $todayAttendance->status : 'not_checked_in';
            $worker->check_in_time = $todayAttendance ? $todayAttendance->check_in : null;
            $worker->check_out_time = $todayAttendance ? $todayAttendance->check_out : null;
            $worker->is_late = $todayAttendance ? $todayAttendance->is_late : false;
            $worker->late_minutes = $todayAttendance ? $todayAttendance->late_minutes : 0;
            $worker->is_early_leave = $todayAttendance ? $todayAttendance->is_early_leave : false;
            $worker->early_leave_minutes = $todayAttendance ? $todayAttendance->early_leave_minutes : 0;
            
            return $worker;
        });

        // Gunakan historyFilters untuk filter form (bukan yang sudah dimodifikasi)
        return view('admin.attendance.index', compact('attendances', 'workers', 'historyFilters', 'workersWithAttendance', 'locations'));
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
            
            // Check if it was an early leave and show appropriate message
            $message = 'Check-out berhasil dicatat';
            
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
                
                $message = "Check-out berhasil dicatat. Perhatian: Anda pulang lebih awal {$earlyText} dari jadwal. Pastikan sudah mendapat izin dari atasan.";
                
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
        $filters = [
            'worker_id' => $workerId,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'status' => $request->status,
            'per_page' => $request->per_page ?? 15,
        ];
        $attendances = $this->attendanceService->getAll($filters);
        return view('admin.attendance.history', compact('worker', 'attendances', 'filters'));
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
}
