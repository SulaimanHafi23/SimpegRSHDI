<?php

// filepath: app/Http/Controllers/Admin/Master/DocumentTypeController.php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\DocumentTypeService;
use App\DTOs\Master\DocumentTypeDTO;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function __construct(
        protected DocumentTypeService $documentTypeService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:document-type.view')->only(['index', 'show']);
        $this->middleware('permission:document-type.create')->only(['create', 'store']);
        $this->middleware('permission:document-type.edit')->only(['edit', 'update']);
        $this->middleware('permission:document-type.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'per_page' => $request->per_page ?? 15,
        ];

        $documentTypes = $this->documentTypeService->getAll($filters);

        return view('admin.master.document-types.index', compact('documentTypes'));
    }

    public function create()
    {
        return view('admin.master.document-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name',
            'description' => 'nullable|string',
            'file_format' => 'nullable|string',
            'max_file_size' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = DocumentTypeDTO::fromRequest($validated);
            $result = $this->documentTypeService->create($dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.document-types.index')
                    ->with('success', $result['message']);
            }

            return back()
                ->withInput()
                ->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $documentType = $this->documentTypeService->findById($id);
            return view('admin.master.document-types.show', compact('documentType'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.document-types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $documentType = $this->documentTypeService->findById($id);
            return view('admin.master.document-types.edit', compact('documentType'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.document-types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name,' . $id,
            'description' => 'nullable|string',
            'file_format' => 'nullable|string',
            'max_file_size' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = DocumentTypeDTO::fromRequest($validated);
            $result = $this->documentTypeService->update($id, $dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.document-types.show', $id)
                    ->with('success', $result['message']);
            }

            return back()
                ->withInput()
                ->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $result = $this->documentTypeService->delete($id);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.document-types.index')
                    ->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
