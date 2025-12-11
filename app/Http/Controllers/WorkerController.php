<?php

namespace App\Http\Controllers;

use App\DTOs\Worker\WorkerDTO;
use App\Http\Requests\Worker\WorkerRequest;
use App\Services\WorkerService;
use App\Services\Master\GenderService;
use App\Services\Master\ReligionService;
use App\Services\Master\PositionService;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function __construct(
        private readonly WorkerService $service,
        private readonly GenderService $genderService,
        private readonly ReligionService $religionService,
        private readonly PositionService $positionService
    ) {
    }

    public function index(Request $request)
    {
        $keyword = $request->input('search');
        $filters = [
            'position_id' => $request->input('position_id'),
            'status' => $request->input('status'),
            'is_active' => $request->input('is_active'),
        ];
        
        $workers = $keyword 
            ? $this->service->search($keyword)
            : $this->service->getAllPaginated(15, $filters);

        $positions = $this->positionService->getAll();
        $statistics = $this->service->getStatistics();

        return view('admin.workers.index', compact(
            'workers',
            'keyword',
            'positions',
            'statistics'
        ));
    }

    public function create()
    {
        $genders = $this->genderService->getAll();
        $religions = $this->religionService->getAll();
        $positions = $this->positionService->getAll();
        
        return view('admin.workers.create', compact(
            'genders',
            'religions',
            'positions'
        ));
    }

    public function store(WorkerRequest $request)
    {
        $dto = WorkerDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.workers.show', $result['data']['worker']->id)
                ->with('success', $result['message'])
                ->with('default_password', $result['data']['default_password']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function show(string $id)
    {
        $worker = $this->service->findById($id);
        return view('admin.workers.show', compact('worker'));
    }

    public function edit(string $id)
    {
        $worker = $this->service->findById($id);
        $genders = $this->genderService->getAll();
        $religions = $this->religionService->getAll();
        $positions = $this->positionService->getAll();
        
        return view('admin.workers.edit', compact(
            'worker',
            'genders',
            'religions',
            'positions'
        ));
    }

    public function update(WorkerRequest $request, string $id)
    {
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
        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.workers.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
