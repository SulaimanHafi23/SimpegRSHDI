<?php

namespace App\Http\Controllers\WorkerDocument;

use App\Http\Controllers\Controller;
use App\Services\WorkerDocument\WorkerDocumentService;
use App\Services\Worker\WorkerService;
use App\Services\Master\DocumentTypeService;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkerDocumentController extends Controller
{
    use DepartmentFilterable;

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

        // Get all active workers with their document statistics
        $departmentId = $this->getManagerDepartmentFilter();
        $workers = $departmentId
            ? $this->workerService->getByDepartment($departmentId)
            : $this->workerService->getAllActive();

        $workersWithDocStats = $workers->map(function($worker) use ($filters) {
            // Get required document types for this worker's department
            // Required = document types that are linked to the department in department_document_type pivot table
            $totalRequired = \App\Models\DocumentType::where('is_active', true)
                ->where(function ($query) use ($worker) {
                    $query->where('is_universal', true)
                        ->orWhereHas('departments', function ($inner) use ($worker) {
                            $inner->where('departments.id', $worker->department_id);
                        });
                })
                ->count();

            // Get uploaded documents count
            $uploadedCount = $worker->workerDocuments()
                ->when(!empty($filters['status']), function($q) use ($filters) {
                    return $q->where('status', $filters['status']);
                })
                ->when(!empty($filters['document_type_id']), function($q) use ($filters) {
                    return $q->where('document_type_id', $filters['document_type_id']);
                })
                ->count();

            // Get verified documents count
            $verifiedCount = $worker->workerDocuments()
                ->where('status', 'verified')
                ->count();

            // Get expired documents count
            $expiredCount = $worker->workerDocuments()
                ->where('status', 'verified')
                ->whereNotNull('expired_date')
                ->whereDate('expired_date', '<', now())
                ->count();

            // Calculate completion percentage
            $completionPercentage = $totalRequired > 0
                ? round(($verifiedCount / $totalRequired) * 100, 1)
                : 0;

            $worker->totalRequired = $totalRequired;
            $worker->uploadedCount = $uploadedCount;
            $worker->verifiedCount = $verifiedCount;
            $worker->expiredCount = $expiredCount;
            $worker->completionPercentage = $completionPercentage;

            return $worker;
        });

        // Apply worker filter if specified
        if (isset($filters['worker_id']) && $filters['worker_id']) {
            $workersWithDocStats = $workersWithDocStats->where('id', $filters['worker_id']);
        }

        // Paginate manually
        $perPage = $filters['per_page'];
        $currentPage = $request->get('page', 1);
        $workersWithDocStats = new \Illuminate\Pagination\LengthAwarePaginator(
            $workersWithDocStats->forPage($currentPage, $perPage),
            $workersWithDocStats->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $documentTypes = $this->documentTypeService->getAllActive();

        return view('admin.workers.documents.index', compact('workersWithDocStats', 'workers', 'documentTypes', 'filters'));
    }

    public function create()
    {
        $departmentId = $this->getManagerDepartmentFilter();
        $workers = $departmentId
            ? $this->workerService->getByDepartment($departmentId)
            : $this->workerService->getAllActive();
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

                if ($documentType->departments->isNotEmpty() && ! $documentType->is_universal) {
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

            if ($dt->is_universal) {
                return true;
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

        if (!$worker) {
            return redirect()->route('admin.worker-documents.index')
                ->with('error', 'Pegawai tidak ditemukan');
        }

        // Get all documents for this worker
        $documents = $this->workerDocumentService->getByWorkerId($workerId);

        // Get required document types for this worker's department
        // Required = document types that are linked to the department in department_document_type pivot table
        $allRequiredDocTypes = \App\Models\DocumentType::where('is_active', true)
            ->where(function ($query) use ($worker) {
                $query->where('is_universal', true)
                    ->orWhereHas('departments', function ($inner) use ($worker) {
                        $inner->where('departments.id', $worker->department_id);
                    });
            })
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $totalRequired = $allRequiredDocTypes->count();
        $uploadedCount = $documents->count();
        $verifiedCount = $documents->where('status', 'verified')->count();
        $pendingCount = $documents->where('status', 'pending')->count();
        $rejectedCount = $documents->where('status', 'rejected')->count();
        $expiredCount = $documents->filter(function($doc) {
            return $doc->status === 'verified'
                && $doc->expired_date
                && \Carbon\Carbon::parse($doc->expired_date)->isPast();
        })->count();

        // Group documents by document type
        $documentsByType = $documents->groupBy('document_type_id');

        // Create document checklist
        $documentChecklist = $allRequiredDocTypes->map(function($docType) use ($documentsByType) {
            $docs = $documentsByType->get($docType->id, collect());
            $latestDoc = $docs->sortByDesc('created_at')->first();

            return [
                'document_type' => $docType,
                'is_uploaded' => $docs->isNotEmpty(),
                'latest_document' => $latestDoc,
                'total_uploads' => $docs->count(),
                'status' => $latestDoc ? $latestDoc->status : 'missing',
                'is_expired' => $latestDoc && $latestDoc->expired_date && \Carbon\Carbon::parse($latestDoc->expired_date)->isPast(),
            ];
        });

        $completionPercentage = $totalRequired > 0
            ? round(($verifiedCount / $totalRequired) * 100, 1)
            : 0;

        return view('admin.workers.documents.worker', compact(
            'worker',
            'documents',
            'documentChecklist',
            'totalRequired',
            'uploadedCount',
            'verifiedCount',
            'pendingCount',
            'rejectedCount',
            'expiredCount',
            'completionPercentage'
        ));
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
