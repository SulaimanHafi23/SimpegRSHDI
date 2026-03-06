<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftSwap\ShiftSwapRequestRequest;
use App\Services\ShiftSwap\ShiftSwapService;
use App\Exports\EmployeeShiftSwapExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
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

        // Get open requests from other workers
        $openRequests = $this->shiftSwapService->getOpenRequests($worker->id);

        // Calculate summary statistics
        $summary = [
            'total' => $items->count(),
            'pending' => $items->where('status', 'pending')->count(),
            'approved' => $items->whereIn('status', ['approved', 'accepted', 'awaiting_approval', 'executed'])->count(),
            'history' => $items->whereIn('status', ['rejected', 'cancelled'])->count(),
            'open_requests' => $openRequests->count(),
        ];

        return view('employee.shift-swaps.index', compact('items', 'summary', 'openRequests'));
    }

    public function create()
    {
        $worker = auth()->user()->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        // Get worker's future shifts for requester
        $requesterShifts = $this->shiftSwapService->getFutureShifts($worker->id);

        // Get all workers for target selection
        $workers = $this->shiftSwapService->getAvailableWorkersForSwap($worker->id);

        // Set uniform requirement: 48 hours (2 days) for all departments
        $minHours = 48;
        $minDays = 2;
        $departmentName = $worker->department->name ?? 'Anda';

        return view('employee.shift-swaps.create', compact('requesterShifts', 'workers', 'minHours', 'minDays', 'departmentName'));
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
                ? 'Permintaan diterima dan menunggu persetujuan manager/HR.'
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

    /**
     * Show form to accept an open request
     */
    public function showAcceptOpen(string $id)
    {
        $worker = auth()->user()->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $swapRequest = \App\Models\ShiftSwapRequest::with(['requester.department', 'requesterShift.shift'])->findOrFail($id);

        // Verify it's an open request and not from current worker
        if ($swapRequest->target_worker_id !== null) {
            return redirect()->route('employee.shift-swaps.index')->with('error', 'Ini bukan open request.');
        }

        if ($swapRequest->requester_id === $worker->id) {
            return redirect()->route('employee.shift-swaps.index')->with('error', 'Anda tidak bisa menerima request sendiri.');
        }

        if ($swapRequest->status !== 'pending') {
            return redirect()->route('employee.shift-swaps.index')->with('error', 'Request ini sudah tidak tersedia.');
        }

        // Get worker's shifts for selection
        $workerShifts = $this->shiftSwapService->getFutureShifts($worker->id);

        // Enrich with effective shift (considering ShiftOverride)
        $this->shiftSwapService->enrichWithEffectiveShifts(collect([$swapRequest]));

        return view('employee.shift-swaps.accept-open', compact('swapRequest', 'workerShifts'));
    }

    /**
     * Accept an open request
     */
    public function acceptOpen(Request $request, string $id)
    {
        $worker = auth()->user()->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $request->validate([
            'target_shift_id' => 'required|exists:worker_shifts,id',
        ]);

        try {
            $this->shiftSwapService->acceptOpenRequest($id, $worker->id, $request->input('target_shift_id'));
            return redirect()->route('employee.shift-swaps.index')->with('success', 'Anda berhasil menerima open request. Menunggu persetujuan manager/HR.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menerima request: ' . $e->getMessage());
        }
    }

    /**
     * Export shift swap data (PDF, Excel, CSV)
     */
    public function export(Request $request)
    {
        $worker = auth()->user()->worker;
        if (!$worker) {
            return redirect()->route('employee.dashboard')->with('error', 'Data pekerja tidak ditemukan.');
        }

        $format = $request->input('format', 'pdf');

        $items = $this->shiftSwapService->listForWorker($worker->id);

        // Apply date filters
        if ($request->filled('date_from')) {
            $items = $items->filter(fn($item) => $item->created_at >= $request->date_from);
        }
        if ($request->filled('date_to')) {
            $items = $items->filter(fn($item) => $item->created_at <= $request->date_to . ' 23:59:59');
        }

        // Filter by partner name
        if ($request->filled('partner_id')) {
            $partnerId = $request->partner_id;
            $items = $items->filter(function ($item) use ($partnerId, $worker) {
                if ($item->requester_id === $worker->id) {
                    return $item->target_worker_id === $partnerId;
                }
                return $item->requester_id === $partnerId;
            });
        }

        $items = $items->values();
        $filters = $request->only(['date_from', 'date_to', 'partner_id']);

        if ($format === 'excel') {
            return Excel::download(
                new EmployeeShiftSwapExport($items, $worker),
                'tukar-shift-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        if ($format === 'csv') {
            return Excel::download(
                new EmployeeShiftSwapExport($items, $worker),
                'tukar-shift-' . now()->format('Y-m-d') . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        }

        // PDF
        $pdf = Pdf::loadView('employee.exports.shift-swap-pdf', [
            'title' => 'Laporan Tukar Shift',
            'worker' => $worker,
            'swaps' => $items,
            'filters' => $filters,
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Tukar_Shift_' . $worker->name . '_' . now()->format('YmdHis') . '.pdf');
    }
}
