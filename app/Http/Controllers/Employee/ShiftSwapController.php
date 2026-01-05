<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftSwap\ShiftSwapRequestRequest;
use App\Services\ShiftSwap\ShiftSwapService;
use Illuminate\Http\Request;

class ShiftSwapController extends Controller
{
    public function __construct(protected ShiftSwapService $shiftSwapService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $worker = auth()->user()->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $items = $this->shiftSwapService->listForWorker($worker->id);

        return view('employee.shift-swaps.index', compact('items'));
    }

    public function create()
    {
        $worker = auth()->user()->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        // Get worker's future shifts for requester
        $requesterShifts = $this->shiftSwapService->getFutureShifts($worker->id);
        
        // Get all workers for target selection
        $workers = $this->shiftSwapService->getAvailableWorkersForSwap($worker->id);

        return view('employee.shift-swaps.create', compact('requesterShifts', 'workers'));
    }

    public function store(ShiftSwapRequestRequest $request)
    {
        $worker = auth()->user()->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $data = $request->validated();
        $data['requester_id'] = $worker->id;

        try {
            $swap = $this->shiftSwapService->createRequest($data);
            return redirect()->route('employee.shift-swaps.index')->with('success','Permintaan tukar shift berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error','Gagal membuat permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Accept swap request (by target worker)
     */
    public function accept(Request $request, string $id)
    {
        $worker = auth()->user()->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        try {
            $swap = $this->shiftSwapService->acceptRequest($id, $worker->id);
            
            $message = $swap->requires_manager_approval 
                ? 'Permintaan diterima dan menunggu persetujuan manager.'
                : 'Permintaan tukar shift diterima.';
            
            return redirect()->route('employee.shift-swaps.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error','Gagal menerima permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Reject swap request (by target worker)
     */
    public function reject(Request $request, string $id)
    {
        $worker = auth()->user()->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $reason = $request->input('reason');

        try {
            $this->shiftSwapService->rejectRequest($id, $worker->id, $reason);
            return redirect()->route('employee.shift-swaps.index')->with('success','Permintaan tukar shift ditolak.');
        } catch (\Exception $e) {
            return back()->with('error','Gagal menolak permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel swap request (by requester)
     */
    public function cancel(Request $request, string $id)
    {
        $worker = auth()->user()->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        try {
            $this->shiftSwapService->cancelRequest($id, $worker->id);
            return redirect()->route('employee.shift-swaps.index')->with('success','Permintaan tukar shift dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error','Gagal membatalkan permintaan: ' . $e->getMessage());
        }
    }
}
