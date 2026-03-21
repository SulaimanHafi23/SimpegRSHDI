<?php

// filepath: app/Http/Controllers/Admin/Master/DocumentTypeController.php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
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
        $perPageInput = (string) ($request->per_page ?? '15');
        $perPage = $perPageInput === 'all'
            ? DocumentType::query()->count()
            : (int) $perPageInput;

        if ($perPage <= 0) {
            $perPage = 15;
        }

        $filters = [
            'search' => $request->search,
            'employment_category' => $request->employment_category,
            'process_type' => $request->process_type,
            'per_page' => $perPage,
        ];

        $documentTypes = $this->documentTypeService->getAll($filters);
        $employmentCategories = DocumentType::getEmploymentCategories();
        $processTypes = DocumentType::getProcessTypes();

        return view('admin.master.document-types.index', compact('documentTypes', 'employmentCategories', 'processTypes'));
    }

    public function create()
    {
        $employmentCategories = DocumentType::getEmploymentCategories();
        $processTypes = DocumentType::getProcessTypes();
        $baseDocumentTypes = DocumentType::query()
            ->whereNull('source_document_type_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.master.document-types.create', compact('employmentCategories', 'processTypes', 'baseDocumentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_format' => 'nullable|string',
            'file_formats' => 'nullable|array',
            'file_formats.*' => 'nullable|string|in:pdf,jpg,jpeg,png,doc,docx',
            'max_file_size' => 'nullable|integer|min:1',
            'employment_category' => 'required|string|in:all,asn,pppk,pppk_paruh_waktu,non_asn,outsourced',
            'process_type' => 'required|string|in:onboarding,promotion,payroll,contract_extension',
            'expiration_buffer_days' => 'nullable|integer|min:0|max:365',
            'requirement_notes' => 'nullable|string',
            'source_document_type_id' => 'nullable|uuid|exists:document_types,id',
            'is_required' => 'nullable|boolean',
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
            $employmentCategories = DocumentType::getEmploymentCategories();
            $processTypes = DocumentType::getProcessTypes();
            $baseDocumentTypes = DocumentType::query()
                ->whereNull('source_document_type_id')
                ->where('id', '!=', $id)
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('admin.master.document-types.edit', compact('documentType', 'employmentCategories', 'processTypes', 'baseDocumentTypes'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.document-types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_format' => 'nullable|string',
            'file_formats' => 'nullable|array',
            'file_formats.*' => 'nullable|string|in:pdf,jpg,jpeg,png,doc,docx',
            'max_file_size' => 'nullable|integer|min:1',
            'employment_category' => 'required|string|in:all,asn,pppk,pppk_paruh_waktu,non_asn,outsourced',
            'process_type' => 'required|string|in:onboarding,promotion,payroll,contract_extension',
            'expiration_buffer_days' => 'nullable|integer|min:0|max:365',
            'requirement_notes' => 'nullable|string',
            'source_document_type_id' => 'nullable|uuid|exists:document_types,id',
            'is_required' => 'nullable|boolean',
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
