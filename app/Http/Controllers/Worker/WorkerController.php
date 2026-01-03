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
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WorkersExport;
use App\Exports\WorkersTemplateExport;
use App\Imports\WorkersImport;

class WorkerController extends Controller
{
    public function __construct(
        private readonly WorkerService $service,
        private readonly ReligionService $religionService,
        private readonly GenderService $genderService,
        private readonly LocationService $locationService,
        private readonly DepartmentService $departmentService
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

        return view('admin.workers.index', compact('workers', 'locations', 'departments', 'filters'));
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

            return view('admin.workers.show', compact('worker', 'attendanceThisMonth', 'totalOvertime'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.workers.index')
                ->with('error', 'Worker tidak ditemukan: ' . $e->getMessage());
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
                'status' => $request->input('status'),
                'employment_status' => $request->input('employment_status'),
                'department_id' => $request->input('department_id'),
            ];

            $filename = 'data-pegawai-' . now()->format('Y-m-d-His') . '.xlsx';
            
            return Excel::download(new WorkersExport($filters), $filename);
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
            
            return back()->with('success', "Berhasil import {$successCount} pegawai"
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $this->authorizePermission('view-workers');

        try {
            $filename = 'template-import-pegawai.xlsx';
            return Excel::download(new WorkersTemplateExport(), $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
