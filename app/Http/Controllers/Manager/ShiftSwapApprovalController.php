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
        // Permission check for manager role handled by route middleware
    }

    /**
     * List pending approvals for manager
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        try {
            $items = $this->shiftSwapService->listPendingApprovalsForManager($user->id);
            return view('manager.shift-swap-approvals.index', compact('items'));
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
