<?php

namespace App\Http\Controllers\Worker;

use App\Traits\DepartmentFilterable;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerShiftHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\WorkerRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WorkersExport;
use App\Exports\WorkersTemplateExport;
use App\Imports\WorkersImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Permission\Models\Role;

class WorkerController extends Controller
{
    use DepartmentFilterable;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:worker.manage');
    }

    public function index(Request $request)
    {
        $this->authorizePermission('worker.manage');

        $managerDeptId = $this->getManagerDepartmentFilter();

        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'employment_status' => $request->input('employment_status'),
            // Manager's department is always forced — cannot be overridden via request
            'department_id' => $managerDeptId ?? $request->input('department_id'),
            'per_page' => $request->input('per_page', 15),
        ];

        $workers = $this->getWorkers($filters);
        $departments = $this->getActiveDepartments();
        $roles = $this->getRoles();

        return view('admin.workers.index', compact('workers', 'departments', 'filters', 'roles'));
    }

    public function show(string $id)
    {
        $this->authorizePermission('worker.manage');

        try {
            $worker = $this->findWorkerById($id);

            // Manager can only view workers in their department
            if (!$this->canManageWorker($id)) {
                abort(403, 'Anda tidak memiliki akses untuk melihat data pegawai ini.');
            }
            // Attendance this month
            $month = now()->month;
            $year = now()->year;
            $attendanceThisMonth = Attendance::query()
                ->where('worker_id', $worker->id)
                ->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year)
                ->count();

            // Recent Leave Requests (last 5)
            $leaveRequests = LeaveRequest::query()
                ->with(['leaveType', 'approver'])
                ->where('worker_id', $worker->id)
                ->orderByDesc('start_date')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            // Shift history
            $shiftHistories = WorkerShiftHistory::query()
                ->where('worker_id', $worker->id)
                ->with(['shift', 'changedByUser'])
                ->orderByDesc('changed_at')
                ->orderByDesc('created_at')
                ->get();

            return view('admin.workers.show', compact(
                'worker',
                'attendanceThisMonth',
                'leaveRequests',
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

            // Create date range for the month
            $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

            $attendances = Attendance::query()
                ->with(['worker.department', 'shift'])
                ->where('worker_id', $worker->id)
                ->whereDate('attendance_date', '>=', $startDate->format('Y-m-d'))
                ->whereDate('attendance_date', '<=', $endDate->format('Y-m-d'))
                ->orderByDesc('attendance_date')
                ->orderByDesc('check_in')
                ->get();

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

        $departments = $this->getActiveDepartments();

        return view('admin.workers.create', compact('departments'));
    }

    public function store(WorkerRequest $request)
    {
        $this->authorizePermission('worker.manage');

        try {
            $this->createWorker($request->validated());

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
            $worker = $this->findWorkerById($id);
            $departments = $this->getActiveDepartments();

            return view('admin.workers.edit', compact('worker', 'departments'));
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
            $this->updateWorker($id, $request->validated());

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
            $this->deleteWorker($id);

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
            $this->resignWorker($id, $validated['resign_date']);

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
                $query = Worker::with(['department']);

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

    private function getActiveDepartments()
    {
        return Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function getRoles()
    {
        return Role::query()
            ->orderBy('name')
            ->get();
    }

    private function findWorkerById(string $id): Worker
    {
        return Worker::query()
            ->with(['department', 'activeWorkerShift.shift', 'workerShifts.shift'])
            ->findOrFail($id);
    }

    private function createWorker(array $data): Worker
    {
        return DB::transaction(function () use ($data) {
            $this->ensureWorkerUniqueness($data['nip'], $data['email']);

            if (!empty($data['photo'])) {
                $data['photo_url'] = $this->savePhoto($data['photo'], $data['nip']);
            }

            $worker = Worker::create($this->workerPayload($data));

            return $worker->fresh(['department']);
        });
    }

    private function updateWorker(string $id, array $data): Worker
    {
        return DB::transaction(function () use ($id, $data) {
            $worker = $this->findWorkerById($id);

            $this->ensureWorkerUniqueness($data['nip'], $data['email'], $worker->id);

            if (!empty($data['photo'])) {
                if ($worker->photo_url && Storage::disk('public')->exists($worker->photo_url)) {
                    Storage::disk('public')->delete($worker->photo_url);
                }

                $data['photo_url'] = $this->savePhoto($data['photo'], $data['nip']);
            } elseif (array_key_exists('photo_url', $data)) {
                unset($data['photo_url']);
            }

            $worker->fill($this->workerPayload($data))->save();

            return $worker->fresh(['department', 'activeWorkerShift.shift', 'workerShifts.shift']);
        });
    }

    private function deleteWorker(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $worker = $this->findWorkerById($id);

            if ($worker->photo_url && Storage::disk('public')->exists($worker->photo_url)) {
                Storage::disk('public')->delete($worker->photo_url);
            }

            return (bool) $worker->delete();
        });
    }

    private function resignWorker(string $id, string $resignDate): Worker
    {
        return DB::transaction(function () use ($id, $resignDate) {
            $worker = $this->findWorkerById($id);
            $worker->update([
                'status' => 'resigned',
                'resign_date' => $resignDate,
            ]);

            return $worker->fresh(['department']);
        });
    }

    private function ensureWorkerUniqueness(string $nip, string $email, ?string $ignoreId = null): void
    {
        $nipQuery = Worker::query()->where('nip', $nip);
        $emailQuery = Worker::query()->where('email', $email);
        $userEmailQuery = User::query()->where('email', $email);

        if ($ignoreId) {
            $nipQuery->where('id', '!=', $ignoreId);
            $emailQuery->where('id', '!=', $ignoreId);
            $userEmailQuery->where('worker_id', '!=', $ignoreId);
        }

        if ($nipQuery->exists()) {
            throw new \Exception('NIP already exists.');
        }

        if ($emailQuery->exists()) {
            throw new \Exception('Email already exists.');
        }

        if ($userEmailQuery->exists()) {
            throw new \Exception('Email already exists.');
        }
    }

    private function workerPayload(array $data): array
    {
        $payload = [
            'nip' => $data['nip'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'birth_place' => $data['birth_place'],
            'birth_date' => $data['birth_date'],
            'address' => $data['address'] ?? null,
            'religion' => $data['religion'],
            'gender' => $data['gender'],
            'department_id' => $data['department_id'],
            'hire_date' => $data['hire_date'],
            'resign_date' => $data['resign_date'] ?? null,
            'employment_status' => $data['employment_status'],
            'status' => $data['status'] ?? 'active',
        ];

        if (array_key_exists('photo_url', $data)) {
            $payload['photo_url'] = $data['photo_url'];
        }

        return $payload;
    }

    private function savePhoto($photo, string $nip): string
    {
        $ext = strtolower($photo->getClientOriginalExtension() ?? 'jpg');
        $filename = sprintf('%s_photo_%s.%s', $nip, now()->format('YmdHis'), $ext);

        return $photo->storeAs('worker-photos', $filename, 'public');
    }
}
