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
            return view('admin.workers.show', compact('worker'));
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
        $locations = $this->locationService->getAll();
        $departments = $this->departmentService->getAllActive();

        return view('admin.workers.create', compact('genders', 'religions', 'locations', 'departments'));
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
            $locations = $this->locationService->getAll();
            $departments = $this->departmentService->getAllActive();

            return view('admin.workers.edit', compact('worker', 'genders', 'religions', 'locations', 'departments'));
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
