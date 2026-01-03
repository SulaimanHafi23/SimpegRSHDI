<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\WorkerDocument;
use Illuminate\Http\Request;

class DocumentApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HR');
    }

    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $documents = WorkerDocument::with(['worker.department', 'worker.position', 'documentType'])
            ->where('status', $status)
            ->latest()
            ->paginate(20);

        return view('approvals.documents.index', compact('documents'));
    }

    public function show(string $id)
    {
        $document = WorkerDocument::with(['worker.department', 'worker.position', 'documentType'])
            ->findOrFail($id);

        return view('approvals.documents.show', compact('document'));
    }

    public function verify(Request $request, string $id)
    {
        $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $document = WorkerDocument::findOrFail($id);

            $document->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
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

            $document->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
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
