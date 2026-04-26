<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\WorkerDocument;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentApprovalController extends Controller
{
    use DepartmentFilterable;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:document.approve');
    }

    public function index(Request $request)
    {
        $departmentId = $this->getManagerDepartmentFilter();

        $filters = [
            'status' => $request->input('status', ''),
            'document_type_id' => $request->input('document_type_id') ?? $request->input('document_type'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'search' => $request->input('search'),
            'per_page' => $request->input('per_page', 20),
        ];

        $query = WorkerDocument::with(['worker.department', 'documentType']);

        // Filter by manager's department
        if ($departmentId) {
            $query->whereHas('worker', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['document_type_id'])) {
            $query->where('document_type_id', $filters['document_type_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $query->whereHas('worker', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $search . '%']);
            });
        }

        $documents = $query->latest()
            ->paginate($filters['per_page'])
            ->appends($filters);

        $baseQuery = WorkerDocument::query();
        if ($departmentId) {
            $baseQuery->whereHas('worker', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        if (!empty($filters['document_type_id'])) {
            $baseQuery->where('document_type_id', $filters['document_type_id']);
        }
        if (!empty($filters['date_from'])) {
            $baseQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $baseQuery->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $baseQuery->whereHas('worker', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $search . '%']);
            });
        }

        $totalDocuments = (clone $baseQuery)->count();
        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $verifiedCount = (clone $baseQuery)->where('status', 'verified')->count();
        $rejectedCount = (clone $baseQuery)->where('status', 'rejected')->count();

        $documentTypes = \App\Models\DocumentType::orderBy('name')->get();

        return view('approvals.documents.index', compact(
            'documents',
            'documentTypes',
            'totalDocuments',
            'pendingCount',
            'verifiedCount',
            'rejectedCount'
        ));
    }

    public function show(string $id)
    {
        $document = WorkerDocument::with(['worker.department', 'documentType', 'verifier'])
            ->findOrFail($id);

        // Department restriction applies only for manager-scoped users.
        $departmentId = $this->getManagerDepartmentFilter();
        if ($departmentId && (string) $document->worker->department_id !== (string) $departmentId) {
            abort(403, 'Unauthorized');
        }

        return view('approvals.documents.show', compact('document'));
    }

    public function verify(Request $request, string $id)
    {
        $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $document = WorkerDocument::findOrFail($id);

            // Department restriction applies only for manager-scoped users.
            $departmentId = $this->getManagerDepartmentFilter();
            if ($departmentId && (string) $document->worker->department_id !== (string) $departmentId) {
                return back()->with('error', 'Anda tidak memiliki akses untuk memverifikasi dokumen ini.');
            }

            $document->update([
                'status' => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'verification_notes' => $request->input('verification_notes'),
            ]);

            return redirect()
                ->route('approvals.documents.index')
                ->with('success', 'Dokumen berhasil diverifikasi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $document = WorkerDocument::findOrFail($id);

            // Department restriction applies only for manager-scoped users.
            $departmentId = $this->getManagerDepartmentFilter();
            if ($departmentId && (string) $document->worker->department_id !== (string) $departmentId) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menolak dokumen ini.');
            }

            $document->update([
                'status' => 'rejected',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            return redirect()
                ->route('approvals.documents.index')
                ->with('success', 'Dokumen telah ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
