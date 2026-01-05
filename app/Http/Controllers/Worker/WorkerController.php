<?php

namespace App\Http\Controllers\Worker;

use App\DTOs\WorkerDTO;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\WorkerRequest;
use App\Services\Master\GenderService;
use App\Services\Worker\WorkerService;
use App\Services\Master\LocationService;
use App\Services\Master\ReligionService;
use App\Services\Master\DepartmentService;
use App\Services\Role\RoleService;
use Illuminate\Support\Facades\Storage;

class WorkerController extends Controller
{
    public function __construct(
        private readonly WorkerService $service,
        private readonly ReligionService $religionService,
        private readonly GenderService $genderService,
        private readonly LocationService $locationService,
        private readonly DepartmentService $departmentService,
        private readonly RoleService $roleService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-workers')->only(['index', 'export']);
        $this->middleware('permission:view-worker-profile')->only(['show']);
        $this->middleware('permission:create-workers')->only(['create', 'store', 'import']);
        $this->middleware('permission:edit-workers')->only(['edit', 'update', 'resign']);
        $this->middleware('permission:delete-workers')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $this->authorizePermission('view-workers');

        $filters = [
            'search' => $request->input('search'),
            'location_id' => $request->input('location_id'),
            'status' => $request->input('status'),
            'employment_status' => $request->input('employment_status'),
            'department_id' => $request->input('department_id'),
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
        $this->authorizePermission('view-worker-profile');

        try {
            $worker = $this->service->getById($id);
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

            return view('admin.workers.show', compact(
                'worker', 
                'attendanceThisMonth', 
                'totalOvertime',
                'leaveRequests',
                'overtimeRequests'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.workers.index')
                ->with('error', 'Worker tidak ditemukan: ' . $e->getMessage());
        }
    }

    public function attendanceHistory(Request $request, string $id)
    {
        $this->authorizePermission('view-worker-profile');

        try {
            $worker = $this->service->getById($id);
            
            // Get month and year from request, default to current month
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);
            
            // Get attendance data for the month
            $attendanceService = app(\App\Services\Attendance\AttendanceService::class);
            $attendances = $attendanceService->getByWorkerId($worker->id, [
                'month' => $month,
                'year' => $year,
                'per_page' => 999, // Get all for the month
            ]);
            
            // Calculate statistics
            $totalPresent = $attendances->where('status', 'present')->count();
            $totalAbsent = $attendances->where('status', 'absent')->count();
            $totalLate = $attendances->where('is_late', true)->count();
            $totalLeave = $attendances->where('status', 'leave')->count();
            
            // Create calendar data structure
            $startDate = \Carbon\Carbon::create($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();
            $daysInMonth = $startDate->daysInMonth;
            
            // Get worker's active shift with relation
            $worker->load(['activeWorkerShift.shift']);
            
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
                $attendance = $attendances->firstWhere('attendance_date', $dateKey);
                
                // Get shift schedule for this date
                $shiftId = $worker->getShiftForDate($date);
                $shift = $shiftId ? \App\Models\Shift::find($shiftId) : null;
                
                $calendarData[] = [
                    'date' => $date,
                    'day' => $day,
                    'dayName' => $date->translatedFormat('l'),
                    'attendance' => $attendance,
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
        $this->authorizePermission('create-workers');

        $genders = $this->genderService->getAllActive();
        $religions = $this->religionService->getAllActive();
        $departments = $this->departmentService->getAllActive();

        return view('admin.workers.create', compact('genders', 'religions', 'departments'));
    }

    public function store(WorkerRequest $request)
    {
        $this->authorizePermission('create-workers');

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
        $this->authorizePermission('edit-workers');

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
        $this->authorizePermission('edit-workers');

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
        $this->authorizePermission('delete-workers');

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
        $this->authorizePermission('edit-workers');

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
        $this->authorizePermission('view-workers');

        try {
            $filters = [
                'search' => $request->input('search'),
                'location_id' => $request->input('location_id'),
                'status' => $request->input('status'),
                'employment_status' => $request->input('employment_status'),
                'department_id' => $request->input('department_id'),
            ];

            // TODO: Implement export functionality
            return back()->with('info', 'Fitur export sedang dalam pengembangan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $this->authorizePermission('create-workers');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            // TODO: Implement import functionality
            return back()->with('info', 'Fitur import sedang dalam pengembangan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
