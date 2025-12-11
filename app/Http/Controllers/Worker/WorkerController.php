<?php

// filepath: app/Http/Controllers/WorkerController.php

namespace App\Http\Controllers\Worker;

use App\DTOs\WorkerDTO;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\WorkerRequest;
use App\Services\Master\GenderService;
use App\Services\Worker\WorkerService;
use App\Services\Master\LocationService;
use App\Services\Master\PositionService;
use App\Services\Master\ReligionService;

class WorkerController extends Controller
{
    public function __construct(
        private readonly WorkerService $service,
        private readonly ReligionService $religionService,
        private readonly GenderService $genderService,
        private readonly PositionService $positionService,
        private readonly LocationService $locationService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-workers')->only(['index']);
        $this->middleware('permission:view-worker-profile')->only(['show']);
        $this->middleware('permission:create-workers')->only(['create', 'store']);
        $this->middleware('permission:edit-workers')->only(['edit', 'update']);
        $this->middleware('permission:delete-workers')->only(['destroy']);
        $this->middleware('permission:view-worker-documents')->only(['documents']);
        $this->middleware('permission:view-worker-attendance')->only(['attendance']);
        $this->middleware('permission:view-worker-leaves')->only(['leaves']);
    }

    public function index(Request $request)
    {
        $this->authorizePermission('view-workers');

        $filters = [
            'search' => $request->input('search'),
            'position_id' => $request->input('position_id'),
            'location_id' => $request->input('location_id'),
            'status' => $request->input('status'),
        ];

        $workers = $this->service->getAllPaginated(15, $filters);
        $positions = $this->positionService->getAll();
        $locations = $this->locationService->getAll();

        return view('admin.workers.index', compact('workers', 'positions', 'locations', 'filters'));
    }

    public function show(string $id)
    {
        $this->authorizePermission('view-worker-profile');

        $worker = $this->service->findById($id);

        return view('admin.workers.show', compact('worker'));
    }

    public function create()
    {
        $this->authorizePermission('create-workers');

        $religions = $this->religionService->getAll();
        $genders = $this->genderService->getAll();
        $positions = $this->positionService->getAll();
        $locations = $this->locationService->getAll();

        return view('admin.workers.create', compact('religions', 'genders', 'positions', 'locations'));
    }

    public function store(WorkerRequest $request)
    {
        $this->authorizePermission('create-workers');

        $dto = WorkerDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.workers.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $this->authorizePermission('edit-workers');

        $worker = $this->service->findById($id);
        $religions = $this->religionService->getAll();
        $genders = $this->genderService->getAll();
        $positions = $this->positionService->getAll();
        $locations = $this->locationService->getAll();

        return view('admin.workers.edit', compact('worker', 'religions', 'genders', 'positions', 'locations'));
    }

    public function update(WorkerRequest $request, string $id)
    {
        $this->authorizePermission('edit-workers');

        $dto = WorkerDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.workers.show', $id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('delete-workers');

        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.workers.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function documents(string $id)
    {
        $this->authorizePermission('view-worker-documents');

        $worker = $this->service->findById($id);
        // Additional logic for documents

        return view('admin.workers.documents', compact('worker'));
    }

    public function attendance(string $id)
    {
        $this->authorizePermission('view-worker-attendance');

        $worker = $this->service->findById($id);
        // Additional logic for attendance

        return view('admin.workers.attendance', compact('worker'));
    }

    public function leaves(string $id)
    {
        $this->authorizePermission('view-worker-leaves');

        $worker = $this->service->findById($id);
        // Additional logic for leaves

        return view('admin.workers.leaves', compact('worker'));
    }
}
