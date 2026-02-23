<?php

namespace App\Http\Controllers\Worker;

use App\DTOs\WorkerDTO;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\WorkerRequest;
use App\Services\Master\GenderService;
use App\Services\Worker\WorkerService;
use App\Services\Master\LocationService;
use App\Services\Master\ReligionService;
use App\Services\Master\DepartmentService;
use App\Services\Role\RoleService;
use App\Models\Department;
use App\Models\Worker;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WorkersExport;
use App\Exports\WorkersTemplateExport;
use App\Imports\WorkersImport;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkerController extends Controller
{
    use DepartmentFilterable;

    public function __construct(
        private readonly WorkerService $service,
        private readonly ReligionService $religionService,
        private readonly GenderService $genderService,
        private readonly LocationService $locationService,
        private readonly DepartmentService $departmentService,
        private readonly RoleService $roleService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:worker.manage');
    }

    public function index(Request $request)
    {
        $this->authorizePermission('worker.manage');

        $managerDeptId = $this->getManagerDepartmentFilter();

        $filters = [
            'search' => $request->input('search'),
            'location_id' => $request->input('location_id'),
            'status' => $request->input('status'),
            'employment_status' => $request->input('employment_status'),
            // Manager's department is always forced — cannot be overridden via request
            'department_id' => $managerDeptId ?? $request->input('department_id'),
            'per_page' => $request->input('per_page', 15),
        ];

    $workers = $this->service->getAll($filters);
    $locations = $this->locationService->getAll();
    $departments = $this->departmentService->getAllActive();
    $roles = $this->roleService->getAll();

    return view('admin.workers.index', compact('workers', 'locations', 'departments', 'filters', 'roles'));
    }

    public function show(string $id)
    {
        $this->authorizePermission('worker.manage');

        try {
            $worker = $this->service->getById($id);

            // Manager can only view workers in their department
            if (!$this->canManageWorker($id)) {
                abort(403, 'Anda tidak memiliki akses untuk melihat data pegawai ini.');
            }
            // Attendance this month
            $month = now()->month;
            $year = now()->year;
            $attendanceService = app(\App\Services\Attendance\AttendanceService::class);
            $attendances = $attendanceService->getByWorkerId($worker->id, [
                'month' => $month,
                'year' => $year,
            ]);
            $attendanceThisMonth = $attendances->count();

            // Total overtime (approved only)
            $overtimeService = app(\App\Services\Overtime\OvertimeRequestService::class);
            $overtimes = $overtimeService->getByWorkerId($worker->id, ['status' => 'approved']);
            $totalOvertime = $overtimes->sum('total_hours');

            // Recent Leave Requests (last 5)
            $leaveService = app(\App\Services\Leave\LeaveRequestService::class);
            $leaveRequests = $leaveService->getByWorkerId($worker->id, [
                'per_page' => 5,
                'sort' => 'start_date',
                'order' => 'desc',
            ]);

            // Recent Overtime Requests (last 5)
            $overtimeRequests = $overtimeService->getByWorkerId($worker->id, [
                'per_page' => 5,
                'sort' => 'overtime_date',
                'order' => 'desc',
            ]);

            // Shift history
            $workerShiftService = app(\App\Services\WorkerShift\WorkerShiftService::class);
            $shiftHistories = $workerShiftService->getShiftHistories($worker->id);

            return view('admin.workers.show', compact(
                'worker',
                'attendanceThisMonth',
                'totalOvertime',
                'leaveRequests',
                'overtimeRequests',
                'shiftHistories'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.workers.index')
                ->with('error', 'Worker tidak ditemukan: ' . $e->getMessage());
        }
    }

    public function attendanceHistory(Request $request, string $id)
    {
        $this->authorizePermission('worker.manage');

        try {
            $worker = $this->service->getById($id);

            // Get month and year from request, default to current month
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);

            // Get attendance data for the month using getAll with filters
            $attendanceService = app(\App\Services\Attendance\AttendanceService::class);

            // Create date range for the month
            $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

            // Get attendances using getAll method with proper filters
            $attendancePaginated = $attendanceService->getAll([
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

            return view('admin.workers.attendance-history', compact(
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
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.workers.show', $id)
                ->with('error', 'Gagal memuat riwayat presensi: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $this->authorizePermission('worker.manage');

        $genders = $this->genderService->getAllActive();
        $religions = $this->religionService->getAllActive();
        $departments = $this->departmentService->getAllActive();

        return view('admin.workers.create', compact('genders', 'religions', 'departments'));
    }

    public function store(WorkerRequest $request)
    {
        $this->authorizePermission('worker.manage');

        try {
            $worker = $this->service->create($request->validated());

            return redirect()
                ->route('admin.workers.index')
                ->with('success', 'Data pekerja berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $this->authorizePermission('worker.manage');

        try {
            $worker = $this->service->getById($id);
            $genders = $this->genderService->getAllActive();
            $religions = $this->religionService->getAllActive();
            $departments = $this->departmentService->getAllActive();

            // dd($worker, $genders, $religions, $departments);

            return view('admin.workers.edit', compact('worker', 'genders', 'religions', 'departments'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.workers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(WorkerRequest $request, string $id)
    {
        $this->authorizePermission('worker.manage');

        try {
            $worker = $this->service->update($id, $request->validated());

            return redirect()
                ->route('admin.workers.show', $id)
                ->with('success', 'Data pekerja berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('worker.manage');

        try {
            $this->service->delete($id);

            return redirect()
                ->route('admin.workers.index')
                ->with('success', 'Data pekerja berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function resign(Request $request, string $id)
    {
        $this->authorizePermission('worker.manage');

        $validated = $request->validate([
            'resign_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        try {
            $this->service->resign($id, $validated['resign_date']);

            return back()->with('success', 'Pekerja berhasil di-resign.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $this->authorizePermission('worker.manage');

        try {
            $filters = [
                'status' => $request->input('status'),
                'employment_status' => $request->input('employment_status'),
                'department_id' => $request->input('department_id') ?? $this->getManagerDepartmentFilter(),
                'search' => $request->input('search'),
            ];

            $format = $request->input('format', 'excel');
            $filename = 'data-pegawai-' . now()->format('Y-m-d-His');

            if ($format === 'pdf') {
                $query = Worker::with(['department', 'gender', 'religion']);

                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                if (!empty($filters['employment_status'])) {
                    $query->where('employment_status', $filters['employment_status']);
                }

                if (!empty($filters['department_id'])) {
                    $query->where('department_id', $filters['department_id']);
                }

                if (!empty($filters['search'])) {
                    $searchTerm = strtolower($filters['search']);
                    $query->where(function ($q) use ($searchTerm) {
                        $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                            ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $searchTerm . '%'])
                            ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $searchTerm . '%']);
                    });
                }

                $workers = $query->orderBy('name')->get();
                $departmentName = null;
                if (!empty($filters['department_id'])) {
                    $departmentName = Department::find($filters['department_id'])->name ?? null;
                }

                $pdf = Pdf::loadView('exports.workers-pdf', [
                    'workers' => $workers,
                    'filters' => $filters,
                    'departmentName' => $departmentName,
                ]);
                $pdf->setPaper('a4', 'landscape');
                return $pdf->download($filename . '.pdf');
            }

            if ($format === 'csv') {
                return Excel::download(new WorkersExport($filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
            }

            return Excel::download(new WorkersExport($filters), $filename . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $this->authorizePermission('worker.manage');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            // TODO: Implement import functionality
            $import = new WorkersImport();
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errors = $import->getErrors();

            if (!empty($errors)) {
                $errorMessage = implode('<br>', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorMessage .= '<br>... dan ' . (count($errors) - 5) . ' error lainnya';
                }

                if ($successCount > 0) {
                    return back()->with('warning', "Berhasil import {$successCount} pegawai, namun ada beberapa error:<br>{$errorMessage}");
                }
                return back()->with('error', "Gagal import pegawai:<br>{$errorMessage}");
            }

            return back()->with('success', "Berhasil import {$successCount} pegawai");
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $this->authorizePermission('worker.manage');

        try {
            $filename = 'template-import-pegawai.xlsx';
            return Excel::download(new WorkersTemplateExport(), $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
