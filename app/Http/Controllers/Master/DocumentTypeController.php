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
        $this->middleware('permission:document-type.manage')->only(['index', 'show']);
        $this->middleware('permission:document-type.manage')->only(['create', 'store']);
        $this->middleware('permission:document-type.manage')->only(['edit', 'update']);
        $this->middleware('permission:document-type.manage')->only('destroy');
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
            'file_formats' => 'nullable|array',
            'file_formats.*' => 'nullable|string|in:pdf,jpg,jpeg,png,doc,docx',
            'max_file_size' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        // Convert file_formats array to comma-separated string if provided
        if (isset($validated['file_formats']) && is_array($validated['file_formats'])) {
            $validated['file_format'] = implode(',', $validated['file_formats']);
        }

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
            'file_formats' => 'nullable|array',
            'file_formats.*' => 'nullable|string|in:pdf,jpg,jpeg,png,doc,docx',
            'max_file_size' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        // Convert file_formats array to comma-separated string if provided
        if (isset($validated['file_formats']) && is_array($validated['file_formats'])) {
            $validated['file_format'] = implode(',', $validated['file_formats']);
        }

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
