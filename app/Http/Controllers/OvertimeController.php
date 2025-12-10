<?php

// filepath: app/Http/Controllers/Admin/Overtime/OvertimeController.php

namespace App\Http\Controllers\Admin\Overtime;

use App\DTOs\Overtime\OvertimeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Overtime\OvertimeRequest;
use App\Services\Overtime\OvertimeService;
use App\Services\Worker\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @method void middleware(string|array $middleware)
 */
class OvertimeController extends Controller
{
    public function __construct(
        private readonly OvertimeService $service,
        private readonly WorkerService $workerService
    ) {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ];
        
        $overtimes = $this->service->getAllPaginated(15, $filters);
        $workers = $this->workerService->getActive();
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
        $overtime = $this->service->findById($id);
        return view('admin.overtime.show', compact('overtime'));
    }

    public function create()
    {
        $workers = $this->workerService->getActive();
        return view('admin.overtime.create', compact('workers'));
    }

    public function store(OvertimeRequest $request)
    {
        $dto = OvertimeDTO::fromRequest($request->validated());
        $result = $this->service->create($dto, $request->file('attachment'));

        if ($result['success']) {
            return redirect()
                ->route('admin.overtime.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $overtime = $this->service->findById($id);
        $workers = $this->workerService->getActive();
        
        return view('admin.overtime.edit', compact('overtime', 'workers'));
    }

    public function update(OvertimeRequest $request, string $id)
    {
        $dto = OvertimeDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto, $request->file('attachment'));

        if ($result['success']) {
            return redirect()
                ->route('admin.overtime.show', $id)
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
                ->route('admin.overtime.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function approve(string $id)
    {
        $result = $this->service->approve($id, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function reject(Request $request, string $id)
    {
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
        $pendingOvertimes = $this->service->getPending();
        return view('admin.overtime.pending', compact('pendingOvertimes'));
    }

    public function workerOvertimeReport(Request $request, string $workerId)
    {
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
        
        if (!$overtime->attachment_url) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        if (!Storage::disk('public')->exists($overtime->attachment_url)) {
            return back()->withErrors(['error' => 'File tidak ditemukan di storage']);
        }

        return Storage::disk('public')->download($overtime->attachment_url);
    }

    /**
     * Export overtime report to Excel/PDF
     */
    public function exportReport(Request $request)
    {
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
