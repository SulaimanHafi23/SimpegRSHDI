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
            'document_type_id' => 'nullable|uuid|exists:document_types,id|required_without:department_document_type_id',
            'department_document_type_id' => 'nullable|uuid|exists:department_document_type,id',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            // Align with DB: use `expired_date` (matches worker_documents.expired_date)
            'expired_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string',
        ]);

        // Defensive: ensure the selected document type is allowed for the worker's department
        $worker = $this->workerService->getById($validated['worker_id']);
        if (!$worker) {
            return back()->withInput()->withErrors(['worker_id' => 'Pegawai tidak ditemukan']);
        }
        // If department_document_type_id was not provided, try to resolve it from document_type_id + worker's department
        if (empty($validated['department_document_type_id']) && !empty($validated['document_type_id'])) {
            $ddt = \App\Models\DepartmentDocumentType::where('document_type_id', $validated['document_type_id'])
                ->where('department_id', $worker->department_id)
                ->first();

            if ($ddt) {
                $validated['department_document_type_id'] = $ddt->id;
            }
        }

        // If department_document_type_id is provided, validate it belongs to worker's department
        if (!empty($validated['department_document_type_id'])) {
            $ddt = \App\Models\DepartmentDocumentType::find($validated['department_document_type_id']);
            if (! $ddt) {
                return back()->withInput()->withErrors(['department_document_type_id' => 'Tipe dokumen untuk departemen tidak ditemukan']);
            }

            if ($ddt->department_id !== $worker->department_id) {
                return back()->withInput()->withErrors(['department_document_type_id' => 'Tipe dokumen ini tidak diperbolehkan untuk departemen pegawai tersebut']);
            }

            // set document_type_id from the ddt for downstream service compatibility
            $validated['document_type_id'] = $ddt->document_type_id;
        } else {
            // No department mapping: fallback to existing document type behavior
            try {
                $documentType = $this->documentTypeService->findById($validated['document_type_id']);
                if ($documentType->relationLoaded('departments') === false) {
                    $documentType->load('departments');
                }

                if ($documentType->departments->isNotEmpty()) {
                    // Document type is mapped to departments but no matching mapping found for this worker
                    return back()->withInput()->withErrors(['document_type_id' => 'Tipe dokumen ini tidak diperbolehkan untuk departemen pegawai tersebut']);
                }
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['document_type_id' => 'Tipe dokumen tidak ditemukan']);
            }
        }
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

    /**
     * Return allowed document types for a given worker (by worker id).
     * This is used by the create view to dynamically populate the document type select.
     */
    public function documentTypesForWorker(Request $request)
    {
        $workerId = $request->worker_id;

        if (! $workerId) {
            return response()->json(['data' => []]);
        }

        $worker = $this->workerService->getById($workerId);
        if (! $worker) {
            return response()->json(['data' => []]);
        }

        // Get all active document types and filter by department mapping if present
        $all = $this->documentTypeService->getAllActive();

        $filtered = $all->filter(function ($dt) use ($worker) {
            // load departments relationship if not loaded
            if ($dt->relationLoaded('departments') === false) {
                $dt->load('departments');
            }

            // If the document type has no departments assigned, treat it as global/allowed
            if ($dt->departments->isEmpty()) {
                return true;
            }

            return $dt->departments->contains('id', $worker->department_id);
        })->values();

        return response()->json(['data' => $filtered]);
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
