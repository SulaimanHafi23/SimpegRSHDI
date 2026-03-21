<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:document.manage');
    }

    /**
     * Display a listing of document templates.
     */
    public function index()
    {
        $templates = DocumentTemplate::with(['documentType', 'creator'])
            ->latest()
            ->paginate(20);

        return view('admin.document-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new document template.
     */
    public function create()
    {
        $documentTypes = DocumentType::where('is_active', true)->orderBy('name')->get();
        $categories = [
            'asn' => 'ASN (Aparatur Sipil Negara)',
            'pppk' => 'PPPK (Perjanjian Kerja)',
            'pppk_paruh_waktu' => 'PPPK Paruh Waktu',
            'non_asn' => 'Non-ASN',
            'outsourced' => 'Outsourced',
            null => 'Semua Kategori',
        ];

        return view('admin.document-templates.create', compact('documentTypes', 'categories'));
    }

    /**
     * Store a newly created document template in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'employment_category' => 'nullable|string|in:asn,pppk,pppk_paruh_waktu,non_asn,outsourced',
            'description' => 'nullable|string|max:1000',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // 10MB max
        ]);

        // Store file
        $file = $request->file('file');
        $path = $file->store('document-templates', 'local');

        // Create template record
        $template = DocumentTemplate::create([
            'document_type_id' => $validated['document_type_id'],
            'employment_category' => $validated['employment_category'] ?? null,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $validated['description'] ?? null,
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.document-templates.show', $template)
            ->with('success', 'Template dokumen berhasil ditambahkan.');
    }

    /**
     * Display the specified document template.
     */
    public function show(DocumentTemplate $template)
    {
        $template->load(['documentType', 'creator', 'updater']);

        return view('admin.document-templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified document template.
     */
    public function edit(DocumentTemplate $template)
    {
        $documentTypes = DocumentType::where('is_active', true)->orderBy('name')->get();
        $categories = [
            'asn' => 'ASN (Aparatur Sipil Negara)',
            'pppk' => 'PPPK (Perjanjian Kerja)',
            'pppk_paruh_waktu' => 'PPPK Paruh Waktu',
            'non_asn' => 'Non-ASN',
            'outsourced' => 'Outsourced',
            null => 'Semua Kategori',
        ];

        return view('admin.document-templates.edit', compact('template', 'documentTypes', 'categories'));
    }

    /**
     * Update the specified document template in storage.
     */
    public function update(Request $request, DocumentTemplate $template)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'employment_category' => 'nullable|string|in:asn,pppk,pppk_paruh_waktu,non_asn,outsourced',
            'description' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
            'is_active' => 'boolean',
        ]);

        // If new file is uploaded
        if ($request->hasFile('file')) {
            // Delete old file
            if ($template->file_path && Storage::disk('local')->exists($template->file_path)) {
                Storage::disk('local')->delete($template->file_path);
            }

            // Store new file
            $file = $request->file('file');
            $path = $file->store('document-templates', 'local');

            $template->update([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }

        // Update other fields
        $template->update([
            'document_type_id' => $validated['document_type_id'],
            'employment_category' => $validated['employment_category'] ?? null,
            'description' => $validated['description'] ?? null,
            'updated_by' => auth()->id(),
            'is_active' => $validated['is_active'] ?? $template->is_active,
        ]);

        return redirect()
            ->route('admin.document-templates.show', $template)
            ->with('success', 'Template dokumen berhasil diperbarui.');
    }

    /**
     * Remove the specified document template from storage.
     */
    public function destroy(DocumentTemplate $template)
    {
        // Delete file
        if ($template->file_path && Storage::disk('local')->exists($template->file_path)) {
            Storage::disk('local')->delete($template->file_path);
        }

        // Delete record
        $template->delete();

        return redirect()
            ->route('admin.document-templates.index')
            ->with('success', 'Template dokumen berhasil dihapus.');
    }

    /**
     * Download a document template.
     */
    public function download(DocumentTemplate $template)
    {
        if (!Storage::disk('local')->exists($template->file_path)) {
            abort(404, 'File template tidak ditemukan.');
        }

        return Storage::disk('local')->download(
            $template->file_path,
            $template->file_name
        );
    }

    /**
     * Get available templates for a worker document upload (JSON API).
     */
    public function getTemplatesForDocument(Request $request)
    {
        $documentTypeId = $request->query('document_type_id');
        $employmentCategory = $request->query('employment_category');

        $templates = DocumentTemplate::query()
            ->where('document_type_id', $documentTypeId)
            ->forCategory($employmentCategory)
            ->active()
            ->with('documentType')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'file_name' => $template->file_name,
                    'file_size_human' => $template->file_size_human,
                    'mime_type' => $template->mime_type,
                    'description' => $template->description,
                    'download_url' => $template->download_url,
                    'created_at' => $template->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'templates' => $templates,
            'count' => $templates->count(),
        ]);
    }
}
