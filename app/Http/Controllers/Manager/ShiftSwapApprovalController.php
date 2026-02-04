<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\ShiftSwap\ShiftSwapService;
use Illuminate\Http\Request;

class ShiftSwapApprovalController extends Controller
{
    public function __construct(protected ShiftSwapService $shiftSwapService)
    {
        $this->middleware('auth');
        $this->middleware('role:Manager|HR|Super Admin');
    }

    /**
     * List pending approvals for manager
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        try {
            $filters = [
                'status' => $request->input('status', ''), // Default empty string untuk tampilkan semua
                'requester_id' => $request->input('requester_id'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'per_page' => $request->input('per_page', 15),
            ];

            $items = $this->shiftSwapService->listPendingApprovalsForManager($user->id, $filters);
            
            // Get statistics
            $statistics = [
                'total' => \App\Models\ShiftSwapRequest::count(),
                'awaiting_approval' => \App\Models\ShiftSwapRequest::where('status', 'awaiting_approval')->count(),
                'approved' => \App\Models\ShiftSwapRequest::where('status', 'approved')->count(),
                'rejected' => \App\Models\ShiftSwapRequest::where('status', 'rejected')->count(),
                'executed' => \App\Models\ShiftSwapRequest::whereNotNull('executed_at')->count(),
            ];

            // Get all workers for filter
            $workers = \App\Models\Worker::orderBy('name')->get();

            return view('manager.shift-swap-approvals.index', compact('items', 'statistics', 'workers', 'filters'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengambil data: ' . $e->getMessage());
        }
    }

    /**
     * Show detail of swap request
     */
    public function show(string $id)
    {
        $swap = \App\Models\ShiftSwapRequest::with([
            'requester.user',
            'requester.department',
            'targetWorker.user',
            'targetWorker.department',
            'requesterShift.shift',
            'targetShift.shift'
        ])->findOrFail($id);

        return view('manager.shift-swap-approvals.show', compact('swap'));
    }

    /**
     * Approve swap request
     */
    public function approve(Request $request, string $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        try {
            $this->shiftSwapService->approveByManager($id, $user->id, $request->input('notes'));
            return redirect()->route('manager.shift-swap-approvals.index')
                ->with('success', 'Permintaan tukar shift disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Reject swap request
     */
    public function reject(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = auth()->user();

        try {
            $this->shiftSwapService->rejectByManager($id, $user->id, $request->input('reason'));
            return redirect()->route('manager.shift-swap-approvals.index')
                ->with('success', 'Permintaan tukar shift ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Execute approved swap
     */
    public function execute(Request $request, string $id)
    {
        $user = auth()->user();

        try {
            $this->shiftSwapService->executeSwap($id, $user->id);
            return redirect()->route('manager.shift-swap-approvals.index')
                ->with('success', 'Pertukaran shift berhasil dieksekusi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengeksekusi pertukaran: ' . $e->getMessage());
        }
    }
}
