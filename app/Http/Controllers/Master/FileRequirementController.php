<?php

namespace App\Http\Controllers\Admin\Master;

use App\DTOs\Master\FileRequirementDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\FileRequirementRequest;
use App\Services\Master\FileRequirementService;
use App\Services\Master\PositionService;
use App\Services\Master\DocumentTypeService;
use Illuminate\Http\Request;

class FileRequirementController extends Controller
{
    public function __construct(
        private readonly FileRequirementService $service,
        private readonly PositionService $positionService,
        private readonly DocumentTypeService $documentTypeService
    ) {
        $this->middleware(['auth', 'role:Super Admin|HR']);
    }

    public function index(Request $request)
    {
        $keyword = $request->input('search');
        $positionId = $request->input('position_id');
        
        $fileRequirements = $keyword 
            ? $this->service->search($keyword)
            : $this->service->getAllPaginated(15, $positionId);

        $positions = $this->positionService->getAll();

        return view('admin.settings.file-requirements.index', compact(
            'fileRequirements',
            'keyword',
            'positions',
            'positionId'
        ));
    }

    public function create()
    {
        $positions = $this->positionService->getAll();
        $documentTypes = $this->documentTypeService->getAll();
        
        return view('admin.settings.file-requirements.create', compact(
            'positions',
            'documentTypes'
        ));
    }

    public function store(FileRequirementRequest $request)
    {
        $dto = FileRequirementDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.settings.file-requirements.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function show(string $id)
    {
        $fileRequirement = $this->service->findById($id);
        return view('admin.settings.file-requirements.show', compact('fileRequirement'));
    }

    public function edit(string $id)
    {
        $fileRequirement = $this->service->findById($id);
        $positions = $this->positionService->getAll();
        $documentTypes = $this->documentTypeService->getAll();
        
        return view('admin.settings.file-requirements.edit', compact(
            'fileRequirement',
            'positions',
            'documentTypes'
        ));
    }

    public function update(FileRequirementRequest $request, string $id)
    {
        $dto = FileRequirementDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.settings.file-requirements.index')
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
                ->route('admin.settings.file-requirements.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
