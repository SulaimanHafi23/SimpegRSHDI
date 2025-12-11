<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Models\Berkas;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $worker = Auth::user()->worker;
        
        if (!$worker) {
            return redirect()->route('workers.dashboard')->with('error', 'Data pegawai tidak ditemukan');
        }

        // Get all documents with file requirement
        $documents = Berkas::with('fileRequirement')
            ->where('worker_id', $worker->id)
            ->orderByDesc('created_at')
            ->get();

        // Calculate stats
        $totalDocuments = $documents->count();
        $approvedDocuments = $documents->where('status', 'Approved')->count();
        $pendingDocuments = $documents->where('status', 'Pending')->count();
        $rejectedDocuments = $documents->where('status', 'Rejected')->count();
        $expiredDocuments = $documents->where('is_expired', true)->count();

        // Get file requirements (document types)
        $fileRequirements = \App\Models\FileRequirment::with('documentType')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('workers.documents.index', compact(
            'documents',
            'totalDocuments',
            'approvedDocuments',
            'pendingDocuments',
            'rejectedDocuments',
            'expiredDocuments',
            'fileRequirements'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_requirement_id' => 'required|exists:file_requirments,id',
            'expired_date' => 'nullable|date',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'notes' => 'nullable|string',
        ]);

        $worker = Auth::user()->worker;

        if (!$worker) {
            return back()->with('error', 'Data pegawai tidak ditemukan');
        }

        // Upload file
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents/workers/' . $worker->id, $filename, 'public');

            // Create document record
            Berkas::create([
                'worker_id' => $worker->id,
                'file_requirement_id' => $request->file_requirement_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'expired_date' => $request->expired_date,
                'notes' => $request->notes,
                'status' => 'Pending',
            ]);

            return redirect()->route('workers.documents')->with('success', 'Dokumen berhasil diupload dan menunggu verifikasi');
        }

        return back()->with('error', 'Gagal mengupload dokumen');
    }

    public function show($id)
    {
        $worker = Auth::user()->worker;
        $document = Berkas::with(['fileRequirement.documentType', 'verifier'])
            ->where('worker_id', $worker->id)
            ->findOrFail($id);

        return view('workers.documents.show', compact('document'));
    }

    public function download($id)
    {
        $worker = Auth::user()->worker;
        $document = Berkas::where('worker_id', $worker->id)->findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File tidak ditemukan');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function destroy($id)
    {
        $worker = Auth::user()->worker;
        $document = Berkas::where('worker_id', $worker->id)->findOrFail($id);

        // Only allow deletion if not approved
        if ($document->status === 'Approved') {
            return back()->with('error', 'Dokumen yang sudah disetujui tidak dapat dihapus');
        }

        // Delete file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus');
    }
}
