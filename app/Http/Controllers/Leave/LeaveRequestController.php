<?php

// filepath: app/Http/Controllers/LeaveRequestController.php

namespace App\Http\Controllers\Leave;

use App\DTOs\LeaveRequestDTO;
use App\Http\Requests\Leave\LeaveRequestRequest;
use App\Services\Leave\LeaveRequestService;
use App\Services\Worker\WorkerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly LeaveRequestService $service,
        private readonly WorkerService $workerService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-leave-requests|view-own-leave-requests')->only(['index', 'show']);
        $this->middleware('permission:create-leave-requests')->only(['create', 'store']);
        $this->middleware('permission:edit-leave-requests')->only(['edit', 'update']);
        $this->middleware('permission:delete-leave-requests')->only(['destroy']);
        $this->middleware('permission:approve-leave-requests')->only(['approve']);
        $this->middleware('permission:reject-leave-requests')->only(['reject']);
        $this->middleware('permission:view-pending-leaves')->only(['pending']);
    }

    public function index(Request $request)
    {
        $this->authorizeAnyPermission(['view-leave-requests', 'view-own-leave-requests']);

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'leave_type' => $request->input('leave_type'),
        ];

        // Apply permission-based filters
        if (auth()->user()->can('view-own-leave-requests') && 
            !auth()->user()->can('view-leave-requests')) {
            $filters['worker_id'] = auth()->user()->worker_id;
        }

        $leaveRequests = $this->service->getAllPaginated(15, $filters);
        
        $workers = auth()->user()->can('view-leave-requests')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);

        return view('admin.leave.index', compact('leaveRequests', 'workers', 'filters'));
    }

    public function show(string $id)
    {
        $this->authorizeAnyPermission(['view-leave-requests', 'view-own-leave-requests']);

        $leaveRequest = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-leave-requests') && 
            !auth()->user()->can('view-leave-requests') &&
            !$this->isOwnData($leaveRequest->worker_id)) {
            abort(403, 'Anda hanya dapat melihat pengajuan cuti Anda sendiri.');
        }

        return view('admin.leave.show', compact('leaveRequest'));
    }

    public function create()
    {
        $this->authorizePermission('create-leave-requests');

        $workers = auth()->user()->can('view-leave-requests')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);

        return view('admin.leave.create', compact('workers'));
    }

    public function store(LeaveRequestRequest $request)
    {
        $this->authorizePermission('create-leave-requests');

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
        $this->authorizePermission('edit-leave-requests');

        $leaveRequest = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-leave-requests') && 
            !auth()->user()->can('edit-leave-requests') &&
            !$this->isOwnData($leaveRequest->worker_id)) {
            abort(403, 'Anda hanya dapat mengedit pengajuan cuti Anda sendiri.');
        }

        $workers = auth()->user()->can('view-leave-requests')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);

        return view('admin.leave.edit', compact('leaveRequest', 'workers'));
    }

    public function update(LeaveRequestRequest $request, string $id)
    {
        $this->authorizePermission('edit-leave-requests');

        $leaveRequest = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-leave-requests') && 
            !auth()->user()->can('edit-leave-requests') &&
            !$this->isOwnData($leaveRequest->worker_id)) {
            abort(403);
        }

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
        $this->authorizePermission('delete-leave-requests');

        $leaveRequest = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-leave-requests') && 
            !auth()->user()->can('delete-leave-requests') &&
            !$this->isOwnData($leaveRequest->worker_id)) {
            abort(403);
        }

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
        $this->authorizePermission('approve-leave-requests');

        $result = $this->service->approve($id, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function reject(Request $request, string $id)
    {
        $this->authorizePermission('reject-leave-requests');

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
        $this->authorizePermission('view-pending-leaves');

        $pendingLeaves = $this->service->getPending();

        return view('admin.leave.pending', compact('pendingLeaves'));
    }

    public function workerLeaveQuota(string $workerId)
    {
        $this->authorizeAnyPermission(['view-leave-requests', 'view-own-leave-requests']);

        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('view-leave-requests');
        }

        $worker = $this->workerService->findById($workerId);
        $year = date('Y');
        $quota = $this->service->getWorkerLeaveQuota($workerId, $year);

        return view('admin.leave.quota', compact('worker', 'quota', 'year'));
    }

    public function downloadAttachment(string $id)
    {
        $leaveRequest = $this->service->findById($id);

        // Check permission
        if (!$this->isOwnData($leaveRequest->worker_id)) {
            $this->authorizePermission('view-leave-requests');
        }

        if (!$leaveRequest->attachment_url) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        if (!Storage::disk('public')->exists($leaveRequest->attachment_url)) {
            return back()->withErrors(['error' => 'File tidak ditemukan di storage']);
        }

        return Storage::disk('public')->download($leaveRequest->attachment_url);
    }
}
