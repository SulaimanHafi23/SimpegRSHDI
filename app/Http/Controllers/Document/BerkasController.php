<?php

// filepath: app/Http/Controllers/BerkasController.php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\DTOs\BerkasDTO;
use App\Http\Requests\Document\BerkasRequest;
use App\Services\Document\BerkasService;
use App\Services\Worker\WorkerService;
use App\Services\Master\DocumentTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BerkasController extends Controller
{
    public function __construct(
        private readonly BerkasService $service,
        private readonly WorkerService $workerService,
        private readonly DocumentTypeService $documentTypeService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-documents|view-own-documents')->only(['index', 'show']);
        $this->middleware('permission:upload-documents')->only(['create', 'store']);
        $this->middleware('permission:edit-documents')->only(['edit', 'update']);
        $this->middleware('permission:delete-documents')->only(['destroy']);
        $this->middleware('permission:verify-documents')->only(['verify']);
        $this->middleware('permission:reject-documents')->only(['reject']);
        $this->middleware('permission:view-pending-documents')->only(['pending']);
        $this->middleware('permission:download-documents')->only(['download', 'preview']);
    }

    public function index(Request $request)
    {
        $this->authorizeAnyPermission(['view-documents', 'view-own-documents']);

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'document_type_id' => $request->input('document_type_id'),
            'status' => $request->input('status'),
        ];

        // Apply permission-based filters
        if (auth()->user()->can('view-own-documents') && 
            !auth()->user()->can('view-documents')) {
            $filters['worker_id'] = auth()->user()->worker_id;
        }

        $documents = $this->service->getAllPaginated(15, $filters);
        
        $workers = auth()->user()->can('view-documents')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);
            
        $documentTypes = $this->documentTypeService->getAll();

        return view('admin.documents.index', compact('documents', 'workers', 'documentTypes', 'filters'));
    }

    public function show(string $id)
    {
        $this->authorizeAnyPermission(['view-documents', 'view-own-documents']);

        $document = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-documents') && 
            !auth()->user()->can('view-documents') &&
            !$this->isOwnData($document->worker_id)) {
            abort(403, 'Anda hanya dapat melihat dokumen Anda sendiri.');
        }

        return view('admin.documents.show', compact('document'));
    }

    public function create()
    {
        $this->authorizePermission('upload-documents');

        $workers = auth()->user()->can('view-documents')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);
            
        $documentTypes = $this->documentTypeService->getAll();

        return view('admin.documents.create', compact('workers', 'documentTypes'));
    }

    public function store(BerkasRequest $request)
    {
        $this->authorizePermission('upload-documents');

        // Check if user can upload for other workers
        if ($request->worker_id !== auth()->user()->worker_id) {
            $this->authorizePermission('view-documents');
        }

        $dto = BerkasDTO::fromRequest($request->validated());
        $result = $this->service->create($dto, $request->file('file'));

        if ($result['success']) {
            return redirect()
                ->route('admin.documents.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $this->authorizePermission('edit-documents');

        $document = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-documents') && 
            !auth()->user()->can('edit-documents') &&
            !$this->isOwnData($document->worker_id)) {
            abort(403, 'Anda hanya dapat mengedit dokumen Anda sendiri.');
        }

        $workers = auth()->user()->can('view-documents')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);
            
        $documentTypes = $this->documentTypeService->getAll();

        return view('admin.documents.edit', compact('document', 'workers', 'documentTypes'));
    }

    public function update(BerkasRequest $request, string $id)
    {
        $this->authorizePermission('edit-documents');

        $document = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-documents') && 
            !auth()->user()->can('edit-documents') &&
            !$this->isOwnData($document->worker_id)) {
            abort(403);
        }

        $dto = BerkasDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto, $request->file('file'));

        if ($result['success']) {
            return redirect()
                ->route('admin.documents.show', $id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('delete-documents');

        $document = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-documents') && 
            !auth()->user()->can('delete-documents') &&
            !$this->isOwnData($document->worker_id)) {
            abort(403);
        }

        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.documents.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function verify(string $id)
    {
        $this->authorizePermission('verify-documents');

        $result = $this->service->verify($id, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function reject(Request $request, string $id)
    {
        $this->authorizePermission('reject-documents');

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        $result = $this->service->reject($id, auth()->id(), $request->rejection_reason);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function pending()
    {
        $this->authorizePermission('view-pending-documents');

        $pendingDocuments = $this->service->getPending();
        return view('admin.documents.pending', compact('pendingDocuments'));
    }

    public function workerDocuments(string $workerId)
    {
        $this->authorizeAnyPermission(['view-documents', 'view-own-documents']);

        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('view-documents');
        }

        $worker = $this->workerService->findById($workerId);
        $documents = $this->service->getByWorker($workerId);

        return view('admin.documents.worker-documents', compact('worker', 'documents'));
    }

    public function checkCompleteness(string $workerId)
    {
        $this->authorizeAnyPermission(['view-documents', 'view-own-documents']);

        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('view-documents');
        }

        $worker = $this->workerService->findById($workerId);
        $completeness = $this->service->checkDocumentCompleteness($workerId);

        return view('admin.documents.completeness', compact('worker', 'completeness'));
    }

    public function download(string $id)
    {
        $this->authorizePermission('download-documents');

        $document = $this->service->findById($id);

        // Check own data permission
        if (!$this->isOwnData($document->worker_id)) {
            $this->authorizePermission('view-documents');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function preview(string $id)
    {
        $this->authorizePermission('download-documents');

        $document = $this->service->findById($id);

        // Check own data permission
        if (!$this->isOwnData($document->worker_id)) {
            $this->authorizePermission('view-documents');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        $path = Storage::disk('public')->path($document->file_path);
        return response()->file($path);
    }
}
