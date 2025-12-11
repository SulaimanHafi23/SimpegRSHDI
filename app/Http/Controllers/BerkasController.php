<?php

namespace App\Http\Controllers;

use App\DTOs\BerkasDTO;
use App\Http\Requests\BerkasRequest;
use App\Services\BerkasService;
use App\Services\WorkerService;
use App\Services\Master\FileRequirementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @method void middleware(string|array $middleware)
 */
class BerkasController extends Controller
{
    public function __construct(
        private readonly BerkasService $service,
        private readonly WorkerService $workerService,
        private readonly FileRequirementService $fileRequirementService
    ) {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->input('worker_id'),
            'verification_status' => $request->input('verification_status'),
        ];
        
        $documents = $this->service->getAllPaginated(15, $filters);
        $workers = $this->workerService->getActive();

        return view('admin.documents.index', compact('documents', 'workers', 'filters'));
    }

    public function show(string $id)
    {
        $document = $this->service->findById($id);
        return view('admin.documents.show', compact('document'));
    }

    public function create()
    {
        $workers = $this->workerService->getActive();
        return view('admin.documents.create', compact('workers'));
    }

    public function store(BerkasRequest $request)
    {
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
        $document = $this->service->findById($id);
        return view('admin.documents.edit', compact('document'));
    }

    public function update(BerkasRequest $request, string $id)
    {
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
        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.documents.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function download(string $id)
    {
        $document = $this->service->findById($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function preview(string $id)
    {
        $document = $this->service->findById($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        return response()->file(Storage::disk('public')->path($document->file_path));
    }

    public function verify(Request $request, string $id)
    {
        $request->validate([
            'verification_notes' => 'nullable|string|max:500',
        ]);

        $result = $this->service->verify($id, auth()->id(), $request->verification_notes);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'verification_notes' => 'required|string|max:500',
        ], [
            'verification_notes.required' => 'Alasan penolakan harus diisi.',
        ]);

        $result = $this->service->reject($id, auth()->id(), $request->verification_notes);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function pending()
    {
        $pendingDocuments = $this->service->getPending();
        return view('admin.documents.pending', compact('pendingDocuments'));
    }

    public function workerDocuments(string $workerId)
    {
        $worker = $this->workerService->findById($workerId);
        $documents = $this->service->getByWorker($workerId);

        return view('admin.documents.worker-documents', compact('worker', 'documents'));
    }

    public function checkCompleteness(string $workerId)
    {
        $worker = $this->workerService->findById($workerId);
        $completeness = $this->service->checkCompleteness($workerId);

        return view('admin.documents.completeness', compact('worker', 'completeness'));
    }

    /**
     * Get file requirements by worker position (for AJAX)
     */
    public function getFileRequirements(Request $request)
    {
        $workerId = $request->input('worker_id');
        
        if (!$workerId) {
            return response()->json(['error' => 'Worker ID required'], 400);
        }

        $worker = $this->workerService->findById($workerId);
        $requirements = $this->fileRequirementService->getByPosition($worker->position_id);

        return response()->json($requirements);
    }
}
