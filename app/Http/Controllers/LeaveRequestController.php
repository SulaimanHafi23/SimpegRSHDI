<?php

namespace App\Http\Controllers;

use App\DTOs\LeaveRequestDTO;
use App\Http\Requests\LeaveRequestRequest;
use App\Services\LeaveRequestService;
use App\Services\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly LeaveRequestService $service,
        private readonly WorkerService $workerService
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->input('worker_id'),
            'leave_type' => $request->input('leave_type'), // Changed from leave_type_id
            'status' => $request->input('status'),
            'year' => $request->input('year'),
            'month' => $request->input('month'),
        ];
        
        $leaveRequests = $this->service->getAllPaginated(15, $filters);
        $workers = $this->workerService->getActive();
        $leaveTypes = $this->service->getAvailableLeaveTypes(); // Get from service
        $statistics = $this->service->getStatistics($filters);

        return view('admin.leave.index', compact(
            'leaveRequests',
            'workers',
            'leaveTypes',
            'filters',
            'statistics'
        ));
    }

    public function show(string $id)
    {
        $leaveRequest = $this->service->findById($id);
        return view('admin.leave.show', compact('leaveRequest'));
    }

    public function create()
    {
        $workers = $this->workerService->getActive();
        $leaveTypes = $this->service->getAvailableLeaveTypes(); // Get from service
        
        return view('admin.leave.create', compact('workers', 'leaveTypes'));
    }

    public function store(LeaveRequestRequest $request)
    {
        $dto = LeaveRequestDTO::fromRequest($request->validated());
        $result = $this->service->create($dto, $request->file('attachment'));

        if ($result['success']) {
            return redirect()
                ->route('admin.leave.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $leaveRequest = $this->service->findById($id);
        $workers = $this->workerService->getActive();
        $leaveTypes = $this->service->getAvailableLeaveTypes(); // Get from service
        
        return view('admin.leave.edit', compact('leaveRequest', 'workers', 'leaveTypes'));
    }

    public function update(LeaveRequestRequest $request, string $id)
    {
        $dto = LeaveRequestDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto, $request->file('attachment'));

        if ($result['success']) {
            return redirect()
                ->route('admin.leave.show', $id)
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
                ->route('admin.leave.index')
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
            'notes' => 'required|string|max:500',
        ], [
            'notes.required' => 'Alasan penolakan harus diisi.',
        ]);

        $result = $this->service->reject($id, auth()->id(), $request->notes);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function downloadAttachment(string $id)
    {
        $leaveRequest = $this->service->findById($id);
        
        if (!$leaveRequest->attachment_url) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        if (!Storage::disk('public')->exists($leaveRequest->attachment_url)) {
            return back()->withErrors(['error' => 'File tidak ditemukan di storage']);
        }

        return Storage::disk('public')->download($leaveRequest->attachment_url);
    }

    public function workerLeaveQuota(Request $request, string $workerId)
    {
        $year = $request->input('year', date('Y'));
        $worker = $this->workerService->findById($workerId);
        $quotas = $this->service->getLeaveQuota($workerId, $year);
        $leaveHistory = $this->service->getByWorker($workerId, $year);

        return view('admin.leave.worker-quota', compact(
            'worker',
            'quotas',
            'leaveHistory',
            'year'
        ));
    }

    public function pending()
    {
        $pendingLeaves = $this->service->getPending();
        return view('admin.leave.pending', compact('pendingLeaves'));
    }
}
