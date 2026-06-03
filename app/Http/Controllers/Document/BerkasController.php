<?php

// filepath: app/Http/Controllers/BerkasController.php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Traits\DepartmentFilterable;
use App\Http\Requests\Document\BerkasRequest;
use App\Models\DepartmentDocumentType;
use App\Models\DocumentType;
use App\Models\Notification;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BerkasController extends Controller
{
    use DepartmentFilterable;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:worker-document.manage|view-own-documents')->only(['index', 'show']);
        $this->middleware('permission:worker-document.manage')->only(['create', 'store']);
        $this->middleware('permission:worker-document.manage')->only(['edit', 'update']);
        $this->middleware('permission:worker-document.manage')->only(['destroy']);
        $this->middleware('permission:worker-document.manage')->only(['verify']);
        $this->middleware('permission:worker-document.manage')->only(['reject']);
        $this->middleware('permission:worker-document.manage')->only(['pending']);
        $this->middleware('permission:worker-document.manage')->only(['download', 'preview']);
    }

    public function index(Request $request)
    {
        $this->authorizeAnyPermission(['view-documents', 'view-own-documents']);

        $departmentId = $this->getManagerDepartmentFilter();
        $canViewAll = Gate::allows('view-documents');
        $canViewOwn = Gate::allows('view-own-documents');
        $user = Auth::user();

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'document_type_id' => $request->input('document_type_id'),
            'status' => $request->input('status'),
            'department_id' => $departmentId,
            'per_page' => (int) ($request->input('per_page', 15)),
        ];

        // Apply permission-based filters
        if ($canViewOwn && !$canViewAll) {
            $filters['worker_id'] = $user?->worker_id;
        }

        $documents = WorkerDocument::with(['worker.department', 'documentType', 'verifier'])
            ->when(!empty($filters['worker_id']), fn($q) => $q->where('worker_id', $filters['worker_id']))
            ->when(!empty($filters['document_type_id']), fn($q) => $q->where('document_type_id', $filters['document_type_id']))
            ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['department_id']), function ($q) use ($filters) {
                $q->whereHas('worker', fn($w) => $w->where('department_id', $filters['department_id']));
            })
            ->latest()
            ->paginate($filters['per_page']);

        // Get workers from user's department if Manager
        if ($departmentId) {
            $workers = $canViewAll
                ? Worker::where('status', 'active')->where('department_id', $departmentId)->with(['department'])->get()
                : collect([$user?->worker])->filter();
        } else {
            $workers = $canViewAll
                ? Worker::where('status', 'active')->with(['department'])->get()
                : collect([$user?->worker])->filter();
        }

        $documentTypes = DocumentType::where('is_active', true)->orderBy('name')->get();

        return view('admin.documents.index', compact('documents', 'workers', 'documentTypes', 'filters'));
    }

    public function show(string $id)
    {
        $this->authorizeAnyPermission(['view-documents', 'view-own-documents']);

        $document = WorkerDocument::with(['worker.department', 'documentType', 'verifier'])->findOrFail($id);

        // Check own data permission
        if (Gate::allows('view-own-documents') &&
            !Gate::allows('view-documents') &&
            !$this->isOwnData($document->worker_id)) {
            abort(403, 'Anda hanya dapat melihat dokumen Anda sendiri.');
        }

        return view('admin.documents.show', compact('document'));
    }

    public function create()
    {
        $this->authorizePermission('worker-document.manage');
        $user = Auth::user();
        $canViewAll = Gate::allows('view-documents');

        $workers = $canViewAll
            ? Worker::where('status', 'active')->with(['department'])->get()
            : collect([$user?->worker])->filter();

        $documentTypes = DocumentType::where('is_active', true)->orderBy('name')->get();

        return view('admin.documents.create', compact('workers', 'documentTypes'));
    }

    public function store(BerkasRequest $request)
    {
        $this->authorizePermission('worker-document.manage');

        // Check if user can upload for other workers
        if ($request->worker_id !== Auth::user()?->worker_id) {
            $this->authorizePermission('worker-document.manage');
        }

        $validated = $request->validated();

        // Backward compatibility: map old field name to current model mapping.
        if (empty($validated['department_document_type_id']) && !empty($validated['file_requirement_id'])) {
            $validated['department_document_type_id'] = $validated['file_requirement_id'];
        }

        $worker = Worker::findOrFail($validated['worker_id']);

        if (!empty($validated['department_document_type_id'])) {
            $ddt = DepartmentDocumentType::find($validated['department_document_type_id']);
            if (!$ddt || $ddt->department_id !== $worker->department_id) {
                throw new \Exception('Tipe dokumen ini tidak diperbolehkan untuk departemen pegawai tersebut.');
            }
            $validated['document_type_id'] = $ddt->document_type_id;
        }

        $file = $request->file('file');
        $filename = sprintf(
            '%s_%s_%s.%s',
            $validated['worker_id'],
            $validated['document_type_id'] ?? 'unknown',
            now()->format('YmdHis'),
            $file->getClientOriginalExtension()
        );

        $filePath = $file->storeAs('worker-documents', $filename, 'public');

        $document = WorkerDocument::create([
            'worker_id' => $validated['worker_id'],
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
            ->route('admin.documents.show', $document->id)
            ->with('success', 'Dokumen berhasil diunggah');
    }

    public function edit(string $id)
    {
        $this->authorizePermission('worker-document.manage');

        $document = WorkerDocument::with(['worker', 'documentType', 'departmentDocumentType'])->findOrFail($id);

        // Check own data permission
        if (Gate::allows('view-own-documents') &&
            !Gate::allows('edit-documents') &&
            !$this->isOwnData($document->worker_id)) {
            abort(403, 'Anda hanya dapat mengedit dokumen Anda sendiri.');
        }

        $user = Auth::user();
        $canViewAll = Gate::allows('view-documents');

        $workers = $canViewAll
            ? Worker::where('status', 'active')->with(['department'])->get()
            : collect([$user?->worker])->filter();

        $documentTypes = DocumentType::where('is_active', true)->orderBy('name')->get();

        return view('admin.documents.edit', compact('document', 'workers', 'documentTypes'));
    }

    public function update(BerkasRequest $request, string $id)
    {
        $this->authorizePermission('worker-document.manage');

        $document = WorkerDocument::findOrFail($id);

        // Check own data permission
        if (Gate::allows('view-own-documents') &&
            !Gate::allows('edit-documents') &&
            !$this->isOwnData($document->worker_id)) {
            abort(403);
        }

        $validated = $request->validated();

        if (empty($validated['department_document_type_id']) && !empty($validated['file_requirement_id'])) {
            $validated['department_document_type_id'] = $validated['file_requirement_id'];
        }

        if (!empty($validated['department_document_type_id'])) {
            $ddt = DepartmentDocumentType::find($validated['department_document_type_id']);
            $worker = Worker::find($validated['worker_id']);
            if (!$ddt || !$worker || $ddt->department_id !== $worker->department_id) {
                throw new \Exception('Tipe dokumen ini tidak diperbolehkan untuk departemen pegawai tersebut.');
            }
            $validated['document_type_id'] = $ddt->document_type_id;
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = sprintf(
                '%s_%s_%s.%s',
                $validated['worker_id'],
                $validated['document_type_id'] ?? 'unknown',
                now()->format('YmdHis'),
                $file->getClientOriginalExtension()
            );

            if (!empty($document->file_path) && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $validated['file_path'] = $file->storeAs('worker-documents', $filename, 'public');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
            $validated['status'] = 'pending';
        }

        unset($validated['file'], $validated['file_requirement_id']);
        $document->update($validated);

        return redirect()
            ->route('admin.documents.show', $id)
            ->with('success', 'Dokumen berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('worker-document.manage');

        $document = WorkerDocument::findOrFail($id);

        // Check own data permission
        if (Gate::allows('view-own-documents') &&
            !Gate::allows('delete-documents') &&
            !$this->isOwnData($document->worker_id)) {
            abort(403);
        }

        if (!empty($document->file_path) && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()
            ->route('admin.documents.index')
            ->with('success', 'Dokumen berhasil dihapus');
    }

    public function verify(string $id)
    {
        $this->authorizePermission('worker-document.manage');

        $verifiedBy = Auth::id();
        if (!$verifiedBy) {
            throw new \Exception('User tidak terautentikasi');
        }

        $document = WorkerDocument::with('documentType')->findOrFail($id);
        $document->update([
            'status' => 'verified',
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
        ]);

        $user = User::where('worker_id', $document->worker_id)->first();
        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id' => $user->id,
                'type' => 'document_verified',
                'data' => [
                    'document_id' => $document->id,
                    'type' => 'document',
                    'action' => 'verified',
                    'title' => 'Dokumen Terverifikasi',
                    'message' => sprintf('Dokumen %s Anda telah diverifikasi.', $document->documentType?->name ?? 'Dokumen'),
                ],
            ]);
        }

        return back()->with('success', 'Dokumen berhasil diverifikasi');
    }

    public function reject(Request $request, string $id)
    {
        $this->authorizePermission('worker-document.manage');

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        $verifiedBy = Auth::id();
        if (!$verifiedBy) {
            throw new \Exception('User tidak terautentikasi');
        }

        $document = WorkerDocument::with('documentType')->findOrFail($id);
        $document->update([
            'status' => 'rejected',
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
            'notes' => $request->rejection_reason,
        ]);

        $user = User::where('worker_id', $document->worker_id)->first();
        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id' => $user->id,
                'type' => 'document_rejected',
                'data' => [
                    'document_id' => $document->id,
                    'type' => 'document',
                    'action' => 'rejected',
                    'reason' => $request->rejection_reason,
                    'title' => 'Dokumen Ditolak',
                    'message' => sprintf(
                        'Dokumen %s Anda ditolak. Alasan: %s',
                        $document->documentType?->name ?? 'Dokumen',
                        $request->rejection_reason
                    ),
                ],
            ]);
        }

        return back()->with('success', 'Dokumen berhasil ditolak');
    }

    public function pending()
    {
        $this->authorizePermission('worker-document.manage');

        $pendingDocuments = WorkerDocument::with(['worker.department', 'documentType'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.documents.pending', compact('pendingDocuments'));
    }

    public function workerDocuments(string $workerId)
    {
        $this->authorizeAnyPermission(['view-documents', 'view-own-documents']);

        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('worker-document.manage');
        }

        $worker = Worker::with(['department', 'user'])->findOrFail($workerId);
        $documents = WorkerDocument::with(['documentType', 'verifier'])
            ->where('worker_id', $workerId)
            ->latest()
            ->get();

        return view('admin.documents.worker-documents', compact('worker', 'documents'));
    }

    public function checkCompleteness(string $workerId)
    {
        $this->authorizeAnyPermission(['view-documents', 'view-own-documents']);

        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('worker-document.manage');
        }

        $worker = Worker::with(['department', 'user'])->findOrFail($workerId);

        $universalDocumentTypes = DocumentType::where('is_active', true)
            ->where('is_universal', true)
            ->pluck('id');

        $departmentDocumentTypes = DepartmentDocumentType::where('department_id', $worker->department_id)
            ->pluck('document_type_id');

        $requiredTypeIds = $universalDocumentTypes
            ->merge($departmentDocumentTypes)
            ->unique()
            ->values();

        $uploadedDocs = WorkerDocument::where('worker_id', $workerId)
            ->whereIn('document_type_id', $requiredTypeIds)
            ->get();

        $verifiedDocs = $uploadedDocs->where('status', 'verified');
        $expiredDocs = $verifiedDocs->filter(fn($doc) => $doc->expired_date && $doc->expired_date->lt(now()));

        $completionPercentage = $requiredTypeIds->count() > 0
            ? round(($verifiedDocs->count() / $requiredTypeIds->count()) * 100, 1)
            : 0;

        $completeness = [
            'total_required' => $requiredTypeIds->count(),
            'uploaded' => $uploadedDocs->count(),
            'verified' => $verifiedDocs->count(),
            'expired' => $expiredDocs->count(),
            'completion_percentage' => $completionPercentage,
            'is_complete' => $requiredTypeIds->count() > 0 && $verifiedDocs->count() >= $requiredTypeIds->count(),
        ];

        return view('admin.documents.completeness', compact('worker', 'completeness'));
    }

    public function download(string $id)
    {
        $this->authorizePermission('worker-document.manage');

        $document = WorkerDocument::findOrFail($id);

        // Check own data permission
        if (!$this->isOwnData($document->worker_id)) {
            $this->authorizePermission('worker-document.manage');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        return response()->download(Storage::disk('public')->path($document->file_path), $document->file_name);
    }

    public function preview(string $id)
    {
        $this->authorizePermission('worker-document.manage');

        $document = WorkerDocument::findOrFail($id);

        // Check own data permission
        if (!$this->isOwnData($document->worker_id)) {
            $this->authorizePermission('worker-document.manage');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        $path = Storage::disk('public')->path($document->file_path);
        return response()->file($path);
    }
}
