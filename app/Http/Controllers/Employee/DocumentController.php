<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\WorkerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display employee's documents
     */
    public function index(Request $request)
    {
        $user = Auth::user();
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

        $documentsQuery = WorkerDocument::with(['worker', 'documentType', 'verifier'])
            ->where('worker_id', $worker->id);

        if (!empty($filters['status'])) {
            $documentsQuery->where('status', $filters['status']);
        }

        if (!empty($filters['document_type_id'])) {
            $documentsQuery->where('document_type_id', $filters['document_type_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $documentsQuery->where(function ($query) use ($search) {
                $query->where('document_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('documentType', function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $documents = $documentsQuery
            ->latest()
            ->paginate($filters['per_page'])
            ->appends($filters);

        $documentTypes = $this->getAllowedDocumentTypes($worker);

        // Calculate summary
        $summaryQuery = WorkerDocument::where('worker_id', $worker->id);
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'pending' => (clone $summaryQuery)->where('status', 'pending')->count(),
            'approved' => (clone $summaryQuery)->where('status', 'verified')->count(),
            'rejected' => (clone $summaryQuery)->where('status', 'rejected')->count(),
        ];

        return view('employee.documents.index', compact('documents', 'documentTypes', 'filters', 'summary'));
    }

    /**
     * Show upload form
     */
    public function create()
    {
        $user = Auth::user();
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
        $workerDocuments = WorkerDocument::with(['worker', 'documentType', 'verifier'])
            ->where('worker_id', $worker->id)
            ->latest()
            ->get();

        $documentStats = $workerDocuments->groupBy('document_type_id')->map(function($docs) {
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
        $user = Auth::user();
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

            $file = $request->file('file');
            $filename = sprintf(
                '%s_%s_%s.%s',
                $worker->id,
                $validated['document_type_id'],
                now()->format('YmdHis'),
                $file->getClientOriginalExtension()
            );

            $filePath = $file->storeAs('worker-documents', $filename, 'public');

            WorkerDocument::create([
                'worker_id' => $worker->id,
                'document_type_id' => $validated['document_type_id'],
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'expired_date' => $validated['expired_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
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
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $document = WorkerDocument::with(['worker', 'documentType', 'verifier'])->findOrFail($id);

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
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $document = WorkerDocument::with(['worker', 'documentType', 'verifier'])->findOrFail($id);

        // Verify ownership
        if ($document->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        $disk = Storage::disk('public');

        if (!$document || !$document->file_path) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        if (!$disk->exists($document->file_path)) {
            if (!Storage::exists($document->file_path)) {
                abort(404, 'File dokumen tidak ditemukan.');
            }
            return response()->download(Storage::path($document->file_path), $document->file_name);
        }

        return response()->download($disk->path($document->file_path), $document->file_name);
    }

    /**
     * Preview document inline for the owner.
     */
    public function preview(string $id)
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            abort(404);
        }

        $document = WorkerDocument::with(['worker', 'documentType', 'verifier'])->findOrFail($id);

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
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        try {
            $document = WorkerDocument::with(['worker', 'documentType', 'verifier'])->findOrFail($id);

            // Verify ownership
            if ($document->worker_id !== $worker->id) {
                abort(403, 'Unauthorized');
            }

            // Only pending can be deleted
            if ($document->status !== 'pending') {
                return back()->with('error', 'Hanya dokumen yang masih pending yang bisa dihapus.');
            }

            if ($document->file_path) {
                $publicDisk = Storage::disk('public');
                if ($publicDisk->exists($document->file_path)) {
                    $publicDisk->delete($document->file_path);
                } elseif (Storage::exists($document->file_path)) {
                    Storage::delete($document->file_path);
                }
            }

            $document->delete();

            return redirect()->route('employee.documents.index')
                ->with('success', 'Dokumen berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }
}
