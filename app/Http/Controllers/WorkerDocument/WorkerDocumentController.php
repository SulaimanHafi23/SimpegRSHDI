<?php

namespace App\Http\Controllers\WorkerDocument;

use App\Http\Controllers\Controller;
use App\Models\DepartmentDocumentType;
use App\Models\DocumentType;
use App\Models\Notification;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerDocument;
use App\Traits\DepartmentFilterable;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkerDocumentController extends Controller
{
    use DepartmentFilterable;

    public function __construct() {}

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->worker_id,
            'worker_name' => trim((string) $request->get('worker_name', '')),
            'document_type_id' => $request->document_type_id,
            'status' => $request->status,
            'per_page' => $request->per_page ?? 15,
        ];

        // Get all active workers with their document statistics
        $departmentId = $this->getManagerDepartmentFilter();
        $workersQuery = Worker::where('status', 'active')->with(['department', 'workerDocuments']);
        if ($departmentId) {
            $workersQuery->where('department_id', $departmentId);
        }
        $workers = $workersQuery->get();

        // Pre-fetch required document type counts per department (1 query)
        $universalCount = DocumentType::where('is_active', true)
            ->where('is_universal', true)
            ->count();

        // Get department-specific counts in a single query
        $deptDocCounts = DB::table('department_document_type')
            ->join('document_types', 'department_document_type.document_type_id', '=', 'document_types.id')
            ->where('document_types.is_active', true)
            ->where('document_types.is_universal', false)
            ->selectRaw('department_document_type.department_id, COUNT(*) as cnt')
            ->groupBy('department_document_type.department_id')
            ->pluck('cnt', 'department_id');

        $workersWithDocStats = $workers->map(function($worker) use ($filters, $universalCount, $deptDocCounts) {
            $totalRequired = $universalCount + ($deptDocCounts->get($worker->department_id, 0));

            // Use the already-loaded relation instead of extra queries
            $docs = $worker->workerDocuments;

            $filteredDocs = $docs;
            if (!empty($filters['status'])) {
                $filteredDocs = $filteredDocs->where('status', $filters['status']);
            }
            if (!empty($filters['document_type_id'])) {
                $filteredDocs = $filteredDocs->where('document_type_id', $filters['document_type_id']);
            }
            $uploadedCount = $filteredDocs->count();

            $verifiedCount = $docs->where('status', 'verified')->count();

            $expiredCount = $docs->where('status', 'verified')
                ->filter(fn($d) => $d->expired_date && $d->expired_date < now())
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

        // Apply worker name filter (case-insensitive)
        if (!empty($filters['worker_name'])) {
            $keyword = mb_strtolower($filters['worker_name']);
            $workersWithDocStats = $workersWithDocStats->filter(function ($worker) use ($keyword) {
                $name = mb_strtolower((string) ($worker->name ?? ''));
                return str_contains($name, $keyword);
            });
        }

        // Paginate manually
        $perPage = (int) ($filters['per_page'] ?? 15);
        if (!in_array($perPage, [5, 10, 15, 25, 50], true)) {
            $perPage = 15;
        }
        $currentPage = $request->get('page', 1);
        $workersWithDocStats = new LengthAwarePaginator(
            $workersWithDocStats->forPage($currentPage, $perPage),
            $workersWithDocStats->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $documentTypes = DocumentType::where('is_active', true)->orderBy('name')->get();

        return view('admin.workers.documents.index', compact('workersWithDocStats', 'workers', 'documentTypes', 'filters'));
    }

    public function create()
    {
        $departmentId = $this->getManagerDepartmentFilter();
        $workersQuery = Worker::where('status', 'active')->with(['department']);
        if ($departmentId) {
            $workersQuery->where('department_id', $departmentId);
        }
        $workers = $workersQuery->get();
        $selectedWorkerId = request('worker_id') ?: Auth::user()?->worker?->id;
        $documentTypes = collect();

        if ($selectedWorkerId) {
            $worker = Worker::find($selectedWorkerId);

            if ($worker) {
                $documentTypes = DocumentType::where('is_active', true)
                    ->where(function ($builder) use ($worker) {
                        $builder->where('is_universal', true);

                        if ($worker->department_id) {
                            $builder->orWhereHas('departments', function ($inner) use ($worker) {
                                $inner->where('departments.id', $worker->department_id);
                            });
                        }
                    })
                    ->orderBy('name')
                    ->get();
            }
        }

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
        $worker = Worker::find($validated['worker_id']);
        if (!$worker) {
            return back()->withInput()->withErrors(['worker_id' => 'Pegawai tidak ditemukan']);
        }
        // If department_document_type_id was not provided, try to resolve it from document_type_id + worker's department
        if (empty($validated['department_document_type_id']) && !empty($validated['document_type_id'])) {
            $ddt = DepartmentDocumentType::where('document_type_id', $validated['document_type_id'])
                ->where('department_id', $worker->department_id)
                ->first();

            if ($ddt) {
                $validated['department_document_type_id'] = $ddt->id;
            }
        }

        // If department_document_type_id is provided, validate it belongs to worker's department
        if (!empty($validated['department_document_type_id'])) {
            $ddt = DepartmentDocumentType::find($validated['department_document_type_id']);
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
                $documentType = DocumentType::find($validated['document_type_id']);
                if (! $documentType) {
                    throw new \Exception('Tipe dokumen tidak ditemukan');
                }

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
            if (!isset($validated['file'])) {
                throw new \Exception('File is required.');
            }

            $file = $validated['file'];
            $workerId = $validated['worker_id'];

            $filename = sprintf(
                '%s_%s_%s.%s',
                $workerId,
                $validated['document_type_id'] ?? 'unknown',
                now()->format('YmdHis'),
                $file->getClientOriginalExtension()
            );

            $filePath = $file->storeAs('worker-documents', $filename, 'public');

            WorkerDocument::create([
                'worker_id' => $workerId,
                'document_type_id' => $validated['document_type_id'],
                'department_document_type_id' => $validated['department_document_type_id'] ?? null,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'expired_date' => $validated['expired_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);

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

        $worker = Worker::find($workerId);
        if (! $worker) {
            return response()->json(['data' => []]);
        }

        $query = DocumentType::where('is_active', true)
            ->where(function ($builder) use ($worker) {
                $builder->where('is_universal', true);

                if ($worker->department_id) {
                    $builder->orWhereHas('departments', function ($inner) use ($worker) {
                        $inner->where('departments.id', $worker->department_id);
                    });
                }
            })
            ->orderBy('name');

        return response()->json(['data' => $query->get()]);
    }

    public function show(string $id)
    {
        $document = WorkerDocument::with(['worker', 'documentType', 'verifier'])->find($id);

        return view('admin.workers.documents.show', compact('document'));
    }

    public function verify(Request $request, string $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            $verifiedBy = Auth::id();
            if (! $verifiedBy) {
                throw new \Exception('User tidak terautentikasi');
            }

            $document = WorkerDocument::with('documentType')->findOrFail($id);
            $document->update([
                'status' => 'verified',
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $user = User::where('worker_id', $document->worker_id)->first();
            if ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id' => $user->id,
                    'type' => 'document_verified',
                    'title' => 'Dokumen Terverifikasi',
                    'message' => sprintf(
                        'Dokumen %s Anda telah diverifikasi.',
                        $document->documentType?->name ?? 'Dokumen'
                    ),
                    'data' => [
                        'document_id' => $document->id,
                        'type' => 'document',
                        'action' => 'verified',
                    ],
                ]);
            }

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
            $verifiedBy = Auth::id();
            if (! $verifiedBy) {
                throw new \Exception('User tidak terautentikasi');
            }

            $document = WorkerDocument::with('documentType')->findOrFail($id);
            $document->update([
                'status' => 'rejected',
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
                'notes' => $validated['notes'],
            ]);

            $user = User::where('worker_id', $document->worker_id)->first();
            if ($user) {
                $message = sprintf(
                    'Dokumen %s Anda ditolak.',
                    $document->documentType?->name ?? 'Dokumen'
                );

                if (!empty($validated['notes'])) {
                    $message .= ' Alasan: ' . $validated['notes'];
                }

                Notification::create([
                    'user_id' => $user->id,
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id' => $user->id,
                    'type' => 'document_rejected',
                    'title' => 'Dokumen Ditolak',
                    'message' => $message,
                    'data' => [
                        'document_id' => $document->id,
                        'type' => 'document',
                        'action' => 'rejected',
                        'reason' => $validated['notes'],
                    ],
                ]);
            }

            return back()->with('success', 'Dokumen berhasil ditolak');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function download(string $id)
    {
        try {
            $document = WorkerDocument::find($id);
            $disk = Storage::disk('public');

            if (!$document || !$document->file_path) {
                throw new \Exception('Dokumen tidak ditemukan.');
            }

            if (!$disk->exists($document->file_path)) {
                if (!Storage::exists($document->file_path)) {
                    throw new \Exception('File not found.');
                }

                return response()->download(Storage::path($document->file_path), $document->file_name);
            }

            return response()->download($disk->path($document->file_path), $document->file_name);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function workerDocuments(string $workerId)
    {
        $worker = Worker::with(['department', 'user', 'activeWorkerShift.shift'])->find($workerId);

        if (!$worker) {
            return redirect()->route('admin.worker-documents.index')
                ->with('error', 'Pegawai tidak ditemukan');
        }

        // Get all documents for this worker
        $documents = WorkerDocument::where('worker_id', $workerId)
            ->with(['documentType', 'verifier'])
            ->latest()
            ->get();

        // Get required document types for this worker's department
        // Required = document types that are linked to the department in department_document_type pivot table
        $allRequiredDocTypes = DocumentType::where('is_active', true)
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
                && Carbon::parse($doc->expired_date)->isPast();
        })->count();

        // Group documents by document type
        $documentsByType = $documents->groupBy('document_type_id');

        // Create document checklist
        $documentChecklist = $allRequiredDocTypes->map(function($docType) use ($documentsByType) {
            $docs = $documentsByType->get($docType->id, collect());
            $latestDoc = $docs->sortByDesc('created_at')->first();
            $versions = $docs->sortBy('created_at')->values()->map(function ($document, $index) {
                return [
                    'version' => $index + 1,
                    'document' => $document,
                ];
            });

            return [
                'document_type' => $docType,
                'is_uploaded' => $docs->isNotEmpty(),
                'latest_document' => $latestDoc,
                'total_uploads' => $docs->count(),
                'versions' => $versions,
                'status' => $latestDoc ? $latestDoc->status : 'missing',
                'is_expired' => $latestDoc && $latestDoc->expired_date && Carbon::parse($latestDoc->expired_date)->isPast(),
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
        $documents = WorkerDocument::whereNotNull('expired_date')
            ->where('expired_date', '<', now())
            ->where('status', 'verified')
            ->with(['worker', 'documentType'])
            ->get();

        return view('admin.workers.documents.expired', compact('documents'));
    }

    public function expiring(Request $request)
    {
        $days = $request->days ?? 30;
        $documents = WorkerDocument::whereNotNull('expired_date')
            ->where('expired_date', '<=', now()->addDays((int) $days))
            ->where('expired_date', '>=', now())
            ->where('status', 'verified')
            ->with(['worker', 'documentType'])
            ->get();

        return view('admin.workers.documents.expiring', compact('documents', 'days'));
    }
}
