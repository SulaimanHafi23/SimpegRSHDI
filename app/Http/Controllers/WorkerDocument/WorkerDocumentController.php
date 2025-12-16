<?php

namespace App\Http\Controllers\WorkerDocument;

use App\Http\Controllers\Controller;
use App\Services\WorkerDocument\WorkerDocumentService;
use App\Services\Worker\WorkerService;
use App\Services\Master\DocumentTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkerDocumentController extends Controller
{
    public function __construct(
        protected WorkerDocumentService $workerDocumentService,
        protected WorkerService $workerService,
        protected DocumentTypeService $documentTypeService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->worker_id,
            'document_type_id' => $request->document_type_id,
            'status' => $request->status,
            'per_page' => $request->per_page ?? 15,
        ];

        $documents = $this->workerDocumentService->getAll($filters);
        $workers = $this->workerService->getAllActive();
        $documentTypes = $this->documentTypeService->getAllActive();

        return view('admin.workers.documents.index', compact('documents', 'workers', 'documentTypes'));
    }

    public function create()
    {
        $workers = $this->workerService->getAllActive();
        $documentTypes = $this->documentTypeService->getAllActive();

        return view('admin.workers.documents.create', compact('workers', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|uuid|exists:workers,id',
            'document_type_id' => 'required|uuid|exists:document_types,id',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_number' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->workerDocumentService->create($validated);

            return redirect()
                ->route('admin.worker-documents.index')
                ->with('success', 'Dokumen berhasil diunggah');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $document = $this->workerDocumentService->getById($id);

        return view('admin.workers.documents.show', compact('document'));
    }

    public function verify(Request $request, string $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            $this->workerDocumentService->verify($id, Auth::id(), $validated['notes'] ?? null);

            return back()->with('success', 'Dokumen berhasil diverifikasi');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'notes' => 'required|string',
        ]);

        try {
            $this->workerDocumentService->reject($id, Auth::id(), $validated['notes']);

            return back()->with('success', 'Dokumen berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function download(string $id)
    {
        try {
            return $this->workerDocumentService->downloadDocument($id);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function workerDocuments(string $workerId)
    {
        $worker = $this->workerService->getById($workerId);
        $documents = $this->workerDocumentService->getByWorkerId($workerId);

        return view('admin.workers.documents.worker', compact('worker', 'documents'));
    }

    public function expired()
    {
        $documents = $this->workerDocumentService->getExpiredDocuments();

        return view('admin.workers.documents.expired', compact('documents'));
    }

    public function expiring(Request $request)
    {
        $days = $request->days ?? 30;
        $documents = $this->workerDocumentService->getExpiringDocuments($days);

        return view('admin.workers.documents.expiring', compact('documents', 'days'));
    }
}
