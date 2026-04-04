<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\WorkerDocument\WorkerDocumentService;
use App\Services\Master\DocumentTypeService;
use App\Models\DocumentType;
use App\Models\WorkerDocument;
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

        $documentTypes = $this->getAllowedDocumentTypes($worker);

        // Calculate summary
        $summaryFilters = ['worker_id' => $worker->id];
        $summary = [
            'total' => $this->documentService->getAll($summaryFilters)->total(),
            'pending' => $this->documentService->getAll(array_merge($summaryFilters, ['status' => 'pending']))->total(),
            'approved' => $this->documentService->getAll(array_merge($summaryFilters, ['status' => 'verified']))->total(),
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

        $documentTypes = $this->getAllowedDocumentTypes($worker);

        // Get active uploaded document types for this worker (pending or verified and not expired)
        $uploadedDocTypes = WorkerDocument::query()
            ->where('worker_id', $worker->id)
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($verifiedQuery) {
                        $verifiedQuery->where('status', 'verified')
                            ->where(function ($expiryQuery) {
                                $expiryQuery->whereNull('expired_date')
                                    ->orWhereDate('expired_date', '>=', now()->toDateString());
                            });
                    });
            })
            ->pluck('document_type_id')
            ->toArray();

        // Get document statistics for each type
        $documentStats = $this->documentService->getAll([
            'worker_id' => $worker->id
        ])->groupBy('document_type_id')->map(function($docs) {
            $expiredDocs = $docs->filter(function ($doc) {
                return $doc->expired_date && $doc->expired_date->isPast();
            });

            $latestExpiredDate = $expiredDocs
                ->sortByDesc('expired_date')
                ->first()?->expired_date;

            return [
                'total' => $docs->count(),
                'approved' => $docs->where('status', 'verified')->count(),
                'pending' => $docs->where('status', 'pending')->count(),
                'rejected' => $docs->where('status', 'rejected')->count(),
                'expired' => $expiredDocs->count(),
                'latest_expired_date' => $latestExpiredDate ? $latestExpiredDate->format('Y-m-d') : null,
                'latest_status' => $docs->sortByDesc('created_at')->first()?->status,
                'latest_date' => $docs->sortByDesc('created_at')->first()?->created_at,
            ];
        });

        return view('employee.documents.create', compact('documentTypes', 'uploadedDocTypes', 'documentStats'));
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

        $allowedDocumentTypes = $this->getAllowedDocumentTypes($worker)->pluck('id')->toArray();

        $validated = $request->validate([
            'document_type_id' => [
                'required',
                'uuid',
                'exists:document_types,id',
                function ($attribute, $value, $fail) use ($allowedDocumentTypes) {
                    if (!in_array($value, $allowedDocumentTypes)) {
                        $fail('Jenis dokumen tidak diizinkan untuk departemen Anda.');
                    }
                }
            ],
            'expired_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            // Check for duplicate active document type
            $hasActiveDocument = WorkerDocument::query()
                ->where('worker_id', $worker->id)
                ->where('document_type_id', $validated['document_type_id'])
                ->where(function ($query) {
                    $query->where('status', 'pending')
                        ->orWhere(function ($verifiedQuery) {
                            $verifiedQuery->where('status', 'verified')
                                ->where(function ($expiryQuery) {
                                    $expiryQuery->whereNull('expired_date')
                                        ->orWhereDate('expired_date', '>=', now()->toDateString());
                                });
                        });
                })
                ->exists();

            if ($hasActiveDocument) {
                $documentType = \App\Models\DocumentType::find($validated['document_type_id']);
                return back()
                    ->withInput()
                    ->with('error', "Anda sudah memiliki dokumen {$documentType->name} yang aktif. Upload ulang hanya bisa dilakukan jika dokumen sebelumnya sudah kadaluarsa atau ditolak.");
            }

            // Pass file and data to service (service will handle file upload)
            $document = $this->documentService->create([
                'worker_id' => $worker->id,
                'document_type_id' => $validated['document_type_id'],
                'file' => $request->file('file'),
                'expired_date' => $validated['expired_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('employee.documents.index')
                ->with('success', 'Dokumen berhasil diupload dan sedang menunggu verifikasi!');

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

    private function getAllowedDocumentTypes($worker)
    {
        if (! $worker) {
            return collect();
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

        return $query->get();
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

        return $this->documentService->downloadDocument($id);
    }

    /**
     * Preview document inline for the owner.
     */
    public function preview(string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            abort(404);
        }

        $document = $this->documentService->getById($id);

        if ($document->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        $path = (string) $document->file_path;
        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        return response()->file($disk->path($path), [
            'Cache-Control' => 'private, max-age=3600',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
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
