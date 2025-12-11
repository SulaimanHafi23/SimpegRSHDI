<?php

// filepath: app/Http/Controllers/Admin/Master/DocumentTypeController.php

namespace App\Http\Controllers\Master;

use App\DTOs\Master\DocumentTypeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\DocumentTypeRequest;
use App\Services\Master\DocumentTypeService;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function __construct(
        private readonly DocumentTypeService $service
    ) {
        $this->middleware(['auth', 'role:Super Admin|HR']);
    }

    public function index(Request $request)
    {
        $keyword = $request->input('search');
        
        $documentTypes = $keyword 
            ? $this->service->search($keyword)
            : $this->service->getAllPaginated();

        return view('admin.master.document-types.index', compact('documentTypes', 'keyword'));
    }

    public function create()
    {
        return view('admin.master.document-types.create');
    }

    public function store(DocumentTypeRequest $request)
    {
        $dto = DocumentTypeDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.document-types.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function show(string $id)
    {
        $documentType = $this->service->findWithFileRequirements($id);
        return view('admin.master.document-types.show', compact('documentType'));
    }

    public function edit(string $id)
    {
        $documentType = $this->service->findById($id);
        return view('admin.master.document-types.edit', compact('documentType'));
    }

    public function update(DocumentTypeRequest $request, string $id)
    {
        $dto = DocumentTypeDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.document-types.index')
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
                ->route('admin.master.document-types.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
