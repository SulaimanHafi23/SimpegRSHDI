<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:document-type.manage')->only(['index', 'show']);
        $this->middleware('permission:document-type.manage')->only(['create', 'store']);
        $this->middleware('permission:document-type.manage')->only(['edit', 'update']);
        $this->middleware('permission:document-type.manage')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = DocumentType::query()->withCount('workerDocuments');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $documentTypes = $query->latest()->paginate($request->per_page ?? 15);

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

        if (isset($validated['file_formats']) && is_array($validated['file_formats'])) {
            $validated['file_format'] = implode(',', $validated['file_formats']);
        }

        try {
            DB::beginTransaction();

            $validated['is_active'] = $request->has('is_active');

            DocumentType::create($validated);

            DB::commit();
            Cache::forget('master_document_types_active');

            return redirect()
                ->route('admin.master.document-types.index')
                ->with('success', 'Tipe dokumen berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating document type: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $documentType = DocumentType::with('workerDocuments')->withCount('workerDocuments')->findOrFail($id);
            return view('admin.master.document-types.show', compact('documentType'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.document-types.index')
                ->with('error', 'Tipe dokumen tidak ditemukan');
        }
    }

    public function edit(string $id)
    {
        try {
            $documentType = DocumentType::findOrFail($id);
            return view('admin.master.document-types.edit', compact('documentType'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.document-types.index')
                ->with('error', 'Tipe dokumen tidak ditemukan');
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

        if (isset($validated['file_formats']) && is_array($validated['file_formats'])) {
            $validated['file_format'] = implode(',', $validated['file_formats']);
        }

        try {
            DB::beginTransaction();

            $documentType = DocumentType::findOrFail($id);
            $validated['is_active'] = $request->has('is_active');

            $documentType->update($validated);

            DB::commit();
            Cache::forget('master_document_types_active');

            return redirect()
                ->route('admin.master.document-types.show', $id)
                ->with('success', 'Tipe dokumen berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating document type: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $documentType = DocumentType::findOrFail($id);

            if ($documentType->workerDocuments()->exists()) {
                throw new \Exception('Tipe dokumen tidak dapat dihapus karena masih digunakan');
            }

            $documentType->delete();

            DB::commit();
            Cache::forget('master_document_types_active');

            return redirect()
                ->route('admin.master.document-types.index')
                ->with('success', 'Tipe dokumen berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting document type: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
