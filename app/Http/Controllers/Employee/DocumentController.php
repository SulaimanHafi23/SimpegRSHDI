<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\WorkerDocument\WorkerDocumentService;
use App\Services\Master\DocumentTypeService;
use App\DTOs\WorkerDocumentDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(
        protected WorkerDocumentService $documentService,
        protected DocumentTypeService $documentTypeService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display employee's documents
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $filters = [
            'worker_id' => $worker->id,
            'document_type_id' => $request->document_type_id,
            'status' => $request->status,
            'search' => $request->search,
            'per_page' => $request->per_page ?? 15,
        ];

        $documents = $this->documentService->getAll($filters);
        $documentTypes = $this->documentTypeService->getActive();

        // Calculate summary
        $summaryFilters = ['worker_id' => $worker->id];
        $summary = [
            'total' => $this->documentService->getAll($summaryFilters)->total(),
            'pending' => $this->documentService->getAll(array_merge($summaryFilters, ['status' => 'pending']))->total(),
            'approved' => $this->documentService->getAll(array_merge($summaryFilters, ['status' => 'approved']))->total(),
            'rejected' => $this->documentService->getAll(array_merge($summaryFilters, ['status' => 'rejected']))->total(),
        ];

        return view('employee.documents.index', compact('documents', 'documentTypes', 'filters', 'summary'));
    }

    /**
     * Show upload form
     */
    public function create()
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $documentTypes = $this->documentTypeService->getActive();

        return view('employee.documents.create', compact('documentTypes'));
    }

    /**
     * Store document
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $validated = $request->validate([
            'document_type_id' => 'required|uuid|exists:document_types,id',
            'expired_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            // Handle file upload first to get file info
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'public');
            $fileSize = $file->getSize();

            $dto = WorkerDocumentDTO::fromRequest([
                'worker_id' => $worker->id,
                'document_type_id' => $validated['document_type_id'],
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'expired_date' => $validated['expired_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);

            $this->documentService->create($dto->toArray());

            return redirect()->route('employee.documents.index')
                ->with('success', 'Dokumen berhasil diupload!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal upload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Show document detail
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $document = $this->documentService->getById($id);

        // Verify ownership
        if ($document->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        return view('employee.documents.show', compact('document'));
    }

    /**
     * Download document
     */
    public function download(string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $document = $this->documentService->getById($id);

        // Verify ownership
        if ($document->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File tidak ditemukan di server. Silakan hubungi administrator.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Delete document (only pending)
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        try {
            $document = $this->documentService->getById($id);

            // Verify ownership
            if ($document->worker_id !== $worker->id) {
                abort(403, 'Unauthorized');
            }

            // Only pending can be deleted
            if ($document->status !== 'pending') {
                return back()->with('error', 'Hanya dokumen yang masih pending yang bisa dihapus.');
            }

            $this->documentService->delete($id);

            return redirect()->route('employee.documents.index')
                ->with('success', 'Dokumen berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }
}
