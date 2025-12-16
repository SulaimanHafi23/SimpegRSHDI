<?php

namespace App\Http\Controllers\Overtime;

use App\DTOs\OvertimeRequestDTO;
use App\Services\Overtime\OvertimeRequestService;
use App\Services\Worker\WorkerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Overtime\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OvertimeController extends Controller
{
    public function __construct(
        private readonly OvertimeRequestService $service,
        private readonly WorkerService $workerService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-overtimes|view-own-overtimes')->only(['index', 'show']);
        $this->middleware('permission:create-overtimes')->only(['create', 'store']);
        $this->middleware('permission:edit-overtimes')->only(['edit', 'update']);
        $this->middleware('permission:delete-overtimes')->only(['destroy']);
        $this->middleware('permission:approve-overtimes')->only(['approve']);
        $this->middleware('permission:reject-overtimes')->only(['reject']);
        $this->middleware('permission:view-pending-overtimes')->only(['pending']);
    }

    public function index(Request $request)
    {
        $this->authorizeAnyPermission(['view-overtimes', 'view-own-overtimes']);

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ];

        // Apply permission-based filters
        if (auth()->user()->can('view-own-overtimes') && 
            !auth()->user()->can('view-overtimes')) {
            $filters['worker_id'] = auth()->user()->worker_id;
        }
        
        $overtimes = $this->service->getAllPaginated(15, $filters);
        
        $workers = auth()->user()->can('view-overtimes')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);
            
        $statistics = $this->service->getStatistics($filters);

        return view('admin.overtime.index', compact(
            'overtimes',
            'workers',
            'filters',
            'statistics'
        ));
    }

    public function show(string $id)
    {
        $this->authorizeAnyPermission(['view-overtimes', 'view-own-overtimes']);

        $overtime = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-overtimes') && 
            !auth()->user()->can('view-overtimes') &&
            !$this->isOwnData($overtime->worker_id)) {
            abort(403, 'Anda hanya dapat melihat lembur Anda sendiri.');
        }

        return view('admin.overtime.show', compact('overtime'));
    }

    public function create()
    {
        $this->authorizePermission('create-overtimes');

        $workers = auth()->user()->can('view-overtimes')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);

        return view('admin.overtime.create', compact('workers'));
    }

    public function store(OvertimeRequest $request)
    {
        $this->authorizePermission('create-overtimes');

        try {
            $dto = OvertimeDTO::fromRequest($request->validated());
            $overtime = $this->service->create($dto->toArray());

            return redirect()
                ->route('admin.overtime.show', $overtime->id)
                ->with('success', 'Pengajuan lembur berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function edit(string $id)
    {
        $this->authorizePermission('edit-overtimes');

        $overtime = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-overtimes') && 
            !auth()->user()->can('edit-overtimes') &&
            !$this->isOwnData($overtime->worker_id)) {
            abort(403, 'Anda hanya dapat mengedit lembur Anda sendiri.');
        }

        $workers = auth()->user()->can('view-overtimes')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);
        
        return view('admin.overtime.edit', compact('overtime', 'workers'));
    }

    public function update(OvertimeRequest $request, string $id)
    {
        $this->authorizePermission('edit-overtimes');

        try {
            $overtime = $this->service->findById($id);

            // Check own data permission
            if (auth()->user()->can('view-own-overtimes') && 
                !auth()->user()->can('edit-overtimes') &&
                !$this->isOwnData($overtime->worker_id)) {
                abort(403);
            }

            $dto = OvertimeDTO::fromRequest($request->validated());
            $this->service->update($id, $dto->toArray());

            return redirect()
                ->route('admin.overtime.show', $id)
                ->with('success', 'Pengajuan lembur berhasil diupdate');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('delete-overtimes');

        try {
            $overtime = $this->service->findById($id);

            // Check own data permission
            if (auth()->user()->can('view-own-overtimes') && 
                !auth()->user()->can('delete-overtimes') &&
                !$this->isOwnData($overtime->worker_id)) {
                abort(403);
            }

            $this->service->delete($id);

            return redirect()
                ->route('admin.overtime.index')
                ->with('success', 'Pengajuan lembur berhasil dihapus');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function approve(string $id)
    {
        $this->authorizePermission('approve-overtimes');

        try {
            $this->service->approve($id, auth()->id());
            return back()->with('success', 'Pengajuan lembur berhasil disetujui');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, string $id)
    {
        $this->authorizePermission('reject-overtimes');

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        try {
            $this->service->reject($id, auth()->id(), $request->rejection_reason);
            return back()->with('success', 'Pengajuan lembur berhasil ditolak');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function pending()
    {
        $this->authorizePermission('view-pending-overtimes');

        $pendingOvertimes = $this->service->getPending();
        return view('admin.overtime.pending', compact('pendingOvertimes'));
    }

    public function workerOvertimeReport(Request $request, string $workerId)
    {
        $this->authorizeAnyPermission(['view-overtimes', 'view-own-overtimes']);

        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('view-overtimes');
        }

        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        
        $worker = $this->workerService->findById($workerId);
        $report = $this->service->getWorkerReport($workerId, $year, $month);

        return view('admin.overtime.worker-report', compact(
            'worker',
            'report',
            'year',
            'month'
        ));
    }

    public function downloadAttachment(string $id)
    {
        $overtime = $this->service->findById($id);
        
        // Check permission
        if (!$this->isOwnData($overtime->worker_id)) {
            $this->authorizePermission('view-overtimes');
        }

        if (!$overtime->attachment_url) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        if (!Storage::disk('public')->exists($overtime->attachment_url)) {
            return back()->withErrors(['error' => 'File tidak ditemukan di storage']);
        }

        return Storage::disk('public')->download($overtime->attachment_url);
    }

    public function exportReport(Request $request)
    {
        $this->authorizePermission('export-reports');

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ];

        $format = $request->input('format', 'pdf'); // pdf or excel
        
        // TODO: Implement Excel/PDF export
        
        return back()->with('info', 'Export feature coming soon');
    }
}
