<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftSwap\ShiftSwapRequestRequest;
use App\Exports\EmployeeShiftSwapExport;
use App\Models\BusinessTrip;
use App\Models\LeaveRequest;
use App\Models\ShiftOverride;
use App\Models\ShiftSwapAuditLog;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerShift;
use App\Notifications\ShiftSwapNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftSwapController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * API endpoint: Get worker shifts for a given date range
     * Used to detect if worker has shift rotation (2+ shifts in the period)
     */
    public function getWorkerShiftsInDateRange(Request $request)
    {
        $workerId = $request->input('worker_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$workerId || !$startDate || !$endDate) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters',
            ], 400);
        }

        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->startOfDay();

            $worker = Worker::find($workerId);
            if (!$worker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pekerja tidak ditemukan.',
                ], 404);
            }

            // Resolve the shift for every date in the period, then collapse consecutive
            // days with the same shift into one segment.
            $segments = [];
            $currentSegmentIndex = null;

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $resolved = $worker->resolveShiftForDate($date);
                $shift = $resolved['shift'] ?? null;

                if (!$shift) {
                    continue;
                }

                $shiftId = $shift->id;
 
                if ($currentSegmentIndex === null || $segments[$currentSegmentIndex]['shift_id'] !== $shiftId) {
                    $segments[] = [
                        'id' => $resolved['worker_shift_id'] ?? $shift->id,
                        'shift_id' => $shiftId,
                        'shift_name' => $shift->name,
                        'shift_time' => sprintf(
                            '%s - %s',
                            Carbon::parse($shift->start_time)->format('H:i'),
                            Carbon::parse($shift->end_time)->format('H:i')
                        ),
                        'effective_from' => $date->format('Y-m-d'),
                        'effective_to' => $date->format('Y-m-d'),
                    ];
                    $currentSegmentIndex = array_key_last($segments);
                } else {
                    $segments[$currentSegmentIndex]['effective_to'] = $date->format('Y-m-d');
                }
            }

            $uniqueShifts = $segments;

            return response()->json([
                'success' => true,
                'shifts' => $uniqueShifts,
                'shift_count' => count($uniqueShifts),
                'has_rotation' => count($uniqueShifts) >= 2,
                'warning' => count($uniqueShifts) >= 2 ? 'Shift pegawai telah berubah harap buat permintaan tukar shift pada waktu yang diinginkan' : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching worker shifts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data shift: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $worker = Auth::user()?->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $items = $this->listForWorker($worker->id);

        // Get open requests from other workers
        $openRequests = $this->getOpenRequests($worker->id);

        // Calculate summary statistics
        $summary = [
            'total' => $items->count(),
            'pending' => $items->whereIn('status', ['pending', 'awaiting_approval', 'manager_verified'])->count(),
            'approved' => $items->whereIn('status', ['approved', 'executed', 'accepted'])->count(),
            'history' => $items->whereIn('status', ['rejected', 'cancelled'])->count(),
            'open_requests' => $openRequests->count(),
        ];

        return view('employee.shift-swaps.index', compact('items', 'summary', 'openRequests'));
    }

    public function create()
    {
        $worker = Auth::user()?->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        // Get worker's future shifts for requester
        $requesterShifts = $this->getFutureShifts($worker->id);

        // Get all workers for target selection
        $workers = $this->getAvailableWorkersForSwap($worker->id);

        // Set uniform requirement: 48 hours (2 days) for all departments
        $minHours = 48;
        $minDays = 2;
        $departmentName = $worker->department->name ?? 'Anda';

        return view('employee.shift-swaps.create', compact('requesterShifts', 'workers', 'minHours', 'minDays', 'departmentName'));
    }

    public function store(ShiftSwapRequestRequest $request)
    {
        $worker = Auth::user()?->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $data = $request->validated();
        $data['requester_id'] = $worker->id;

        try {
            $this->createRequest($data);
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
        $worker = Auth::user()?->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        try {
            $this->acceptRequest($id, $worker->id);
            return redirect()->route('employee.shift-swaps.index')
                ->with('success', 'Permintaan diterima dan menunggu persetujuan Manager/HR/Admin. Jadwal belum berubah sebelum approval final.');
        } catch (\Exception $e) {
            return back()->with('error','Gagal menerima permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Reject swap request (by target worker)
     */
    public function reject(Request $request, string $id)
    {
        $worker = Auth::user()?->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $reason = $request->input('reason');

        try {
            $this->rejectRequest($id, $worker->id, $reason);
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
        $worker = Auth::user()?->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        try {
            $this->cancelRequest($id, $worker->id);
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
        $worker = Auth::user()?->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $swapRequest = ShiftSwapRequest::with(['requester.department', 'requesterShift.shift'])->findOrFail($id);

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
        $workerShifts = $this->getFutureShifts($worker->id);

        // Enrich with effective shift (considering ShiftOverride)
        $this->enrichWithEffectiveShifts(collect([$swapRequest]));

        return view('employee.shift-swaps.accept-open', compact('swapRequest', 'workerShifts'));
    }

    /**
     * Accept an open request
     */
    public function acceptOpen(Request $request, string $id)
    {
        $worker = Auth::user()?->worker;
        if (!$worker) return redirect()->route('employee.dashboard')->with('error','Data pekerja tidak ditemukan.');

        $request->validate([
            'target_shift_id' => 'required|exists:worker_shifts,id',
        ]);

        try {
            $this->acceptOpenRequest($id, $worker->id, $request->input('target_shift_id'));
            return redirect()->route('employee.shift-swaps.index')->with('success', 'Anda berhasil menerima open request. Menunggu persetujuan Manager/HR/Admin. Jadwal belum berubah sebelum approval final.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menerima request: ' . $e->getMessage());
        }
    }

    /**
     * Export shift swap data (PDF, Excel, CSV)
     */
    public function export(Request $request)
    {
        $worker = Auth::user()?->worker;
        if (!$worker) {
            return redirect()->route('employee.dashboard')->with('error', 'Data pekerja tidak ditemukan.');
        }

        $format = $request->input('format', 'pdf');

        $items = $this->listForWorker($worker->id);

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

    private function listForWorker(string $workerId)
    {
        $items = ShiftSwapRequest::where(function ($query) use ($workerId) {
            $query->where('requester_id', $workerId)
                ->orWhere('target_worker_id', $workerId);
        })
        ->orderByDesc('created_at')
        ->with([
            'requester.department',
            'targetWorker.department',
            'requesterShift.shift',
            'targetShift.shift',
        ])
        ->get();

        $this->enrichWithEffectiveShifts($items);

        return $items;
    }

    private function enrichWithEffectiveShifts($items): void
    {
        $items->each(function ($item) {
            $swapDate = $item->swap_date ?? $item->swap_start_date;
            if (!$swapDate) {
                return;
            }

            $dateStr = $swapDate->format('Y-m-d');

            if ($item->requester_id) {
                $overrideRequester = ShiftOverride::where('worker_id', $item->requester_id)
                    ->where('override_date', $dateStr)
                    ->with('shift')
                    ->first();
                $item->setAttribute('effective_requester_shift', $overrideRequester?->shift ?? $item->requesterShift?->shift);
            }

            if ($item->target_worker_id) {
                $overrideTarget = ShiftOverride::where('worker_id', $item->target_worker_id)
                    ->where('override_date', $dateStr)
                    ->with('shift')
                    ->first();
                $item->setAttribute('effective_target_shift', $overrideTarget?->shift ?? $item->targetShift?->shift);
            }
        });
    }

    private function getOpenRequests(string $excludeWorkerId)
    {
        $worker = Worker::find($excludeWorkerId);

        return ShiftSwapRequest::whereNull('target_worker_id')
            ->where('requester_id', '!=', $excludeWorkerId)
            ->where('status', 'pending')
            ->when($worker?->department_id, function ($query) use ($worker) {
                $query->whereHas('requester', function ($requesterQuery) use ($worker) {
                    $requesterQuery->where('department_id', $worker->department_id);
                });
            })
            ->orderByDesc('created_at')
            ->with(['requester.department', 'requesterShift.shift'])
            ->get();
    }

    private function getFutureShifts(string $workerId)
    {
        $today = now()->format('Y-m-d');

        return WorkerShift::where('worker_id', $workerId)
            ->where('is_active', true)
            ->where('effective_from', '<=', $today)  // Shift must have started
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_until')
                ->orWhere('effective_until', '>=', $today);  // Shift must still be active or no end date
            })
            ->with('shift')
            ->orderBy('effective_from', 'desc')  // Show latest first
            ->get();
    }

    private function getAvailableWorkersForSwap(string $workerId)
    {
        $worker = Worker::find($workerId);
        if (!$worker) {
            return collect();
        }

        return Worker::with('department')
            ->where('id', '!=', $workerId)
            ->where('status', 'active')
            ->where('department_id', $worker->department_id)
            ->orderBy('name')
            ->get();
    }

    private function createRequest(array $data): ShiftSwapRequest
    {
        $requester = Worker::find($data['requester_id'] ?? null);
        if (!$requester) {
            throw new \Exception('Data requester tidak ditemukan.');
        }

        $requesterShift = WorkerShift::find($data['requester_shift_id'] ?? null);
        if (!$requesterShift) {
            throw new \Exception('Jadwal requester tidak ditemukan.');
        }

        $targetWorker = null;
        if (!empty($data['target_worker_id'])) {
            $targetWorker = Worker::find($data['target_worker_id']);
            if (!$targetWorker) {
                throw new \Exception('Target worker tidak ditemukan.');
            }

            if ($requester->department_id !== $targetWorker->department_id) {
                throw new \Exception('Tukar shift hanya dapat dilakukan dengan pegawai dari departemen yang sama.');
            }
        }

        if (!empty($data['target_shift_id'])) {
            $targetShift = WorkerShift::find($data['target_shift_id']);
            if (!$targetShift) {
                throw new \Exception('Target shift tidak ditemukan.');
            }
        }

        $swapDates = $this->extractSwapDatesFromArray($data);
        $this->validateWorkerNoAbsenceConflicts($requester->id, $swapDates, 'mengajukan tukar shift');

        $payload = $data;
        if (($payload['swap_type'] ?? 'single_date') === 'single_date') {
            $singleDate = $payload['swap_start_date'] ?? $payload['swap_date'] ?? null;
            $payload['swap_start_date'] = $singleDate;
            $payload['swap_end_date'] = $singleDate;
        }

        $payload['status'] = 'pending';
        $payload['requires_manager_approval'] = true;
        $payload['requested_at'] = Carbon::now();

        DB::beginTransaction();
        try {
            $swap = ShiftSwapRequest::create($payload);

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'created',
                newStatus: 'pending',
                userId: Auth::id(),
                notes: 'Swap request created',
                metadata: [
                    'requester_id' => $requester->id,
                    'requester_name' => $requester->name,
                    'target_worker_id' => $targetWorker?->id,
                    'target_worker_name' => $targetWorker?->name,
                    'requires_manager_approval' => true,
                ]
            );

            Log::info('Shift swap request created', [
                'swap_id' => $swap->id,
                'requester_id' => $requester->id,
                'target_worker_id' => $targetWorker?->id,
                'requires_manager_approval' => true,
            ]);

            if ($targetWorker && $targetWorker->user) {
                $targetWorker->user->notify(new ShiftSwapNotification($swap, 'created'));
            }

            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create shift swap request', [
                'error' => $e->getMessage(),
                'requester_id' => $requester->id,
            ]);
            throw $e;
        }
    }

    private function acceptOpenRequest(string $swapId, string $workerId, string $targetShiftId): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        if ($swap->target_worker_id !== null) {
            throw new \Exception('Ini bukan open request.');
        }

        if ($swap->requester_id === $workerId) {
            throw new \Exception('Anda tidak bisa menerima request Anda sendiri.');
        }

        if ($swap->status !== 'pending') {
            throw new \Exception('Request ini sudah tidak tersedia.');
        }

        $targetShift = WorkerShift::where('id', $targetShiftId)
            ->where('worker_id', $workerId)
            ->first();

        if (!$targetShift) {
            throw new \Exception('Shift yang dipilih tidak valid.');
        }

        $requester = $swap->requester;
        $acceptingWorker = Worker::findOrFail($workerId);

        if (!$requester || $requester->department_id !== $acceptingWorker->department_id) {
            throw new \Exception('Open request hanya dapat diterima oleh pegawai dari departemen yang sama.');
        }

        $this->validateWorkerNoAbsenceConflicts(
            $workerId,
            $this->getSwapDates($swap),
            'menerima open request tukar shift'
        );

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->target_worker_id = $workerId;
            $swap->target_shift_id = $targetShiftId;
            $swap->status = 'awaiting_approval';
            $swap->requires_manager_approval = true;
            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'open_request_accepted',
                newStatus: $swap->status,
                userId: Auth::id(),
                oldStatus: $oldStatus,
                notes: 'Open request accepted by another worker',
                metadata: [
                    'target_worker_id' => $workerId,
                    'target_shift_id' => $targetShiftId,
                    'requires_manager_approval' => true,
                ]
            );

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'open_request_accepted'));
            }

            $this->notifyFinalApprovers($swap);

            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function acceptRequest(string $swapId, string $workerId): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        if ($swap->target_worker_id !== $workerId) {
            throw new \Exception('Anda tidak berhak menerima swap request ini.');
        }

        if ($swap->status !== 'pending') {
            throw new \Exception('Swap request ini tidak dalam status pending.');
        }

        $this->validateWorkerNoAbsenceConflicts(
            $workerId,
            $this->getSwapDates($swap),
            'menerima permintaan tukar shift'
        );

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'awaiting_approval';
            $swap->requires_manager_approval = true;
            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'accepted',
                newStatus: $swap->status,
                userId: Auth::id(),
                oldStatus: $oldStatus,
                notes: 'Target worker accepted the swap request',
                metadata: [
                    'target_worker_id' => $workerId,
                    'requires_manager_approval' => true,
                ]
            );

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'accepted'));
            }

            $this->notifyFinalApprovers($swap);

            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function rejectRequest(string $swapId, string $workerId, ?string $reason = null): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        if ($swap->target_worker_id !== $workerId) {
            throw new \Exception('Anda tidak berhak menolak swap request ini.');
        }

        if (!in_array($swap->status, ['pending', 'awaiting_approval'])) {
            throw new \Exception('Swap request tidak dapat ditolak pada status saat ini.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'rejected';

            if ($reason) {
                $metadata = $swap->metadata ?? [];
                $metadata['rejection_reason'] = $reason;
                $metadata['rejected_by'] = 'target_worker';
                $metadata['rejected_at'] = Carbon::now()->toDateTimeString();
                $swap->metadata = $metadata;
            }

            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'rejected',
                newStatus: 'rejected',
                userId: Auth::id(),
                oldStatus: $oldStatus,
                notes: $reason ?? 'Target worker rejected the swap request',
                metadata: [
                    'target_worker_id' => $workerId,
                    'rejected_by' => 'target_worker',
                ]
            );

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'rejected', $reason));
            }

            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function cancelRequest(string $swapId, string $workerId): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);

        if ($swap->requester_id !== $workerId) {
            throw new \Exception('Hanya requester yang dapat membatalkan swap request.');
        }

        if (in_array($swap->status, ['executed', 'cancelled'])) {
            throw new \Exception('Swap request tidak dapat dibatalkan pada status saat ini.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'cancelled';
            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'cancelled',
                newStatus: 'cancelled',
                userId: Auth::id(),
                oldStatus: $oldStatus,
                notes: 'Requester cancelled the swap request',
                metadata: [
                    'requester_id' => $workerId,
                    'cancelled_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'cancelled'));
            }

            if ($swap->manager_id) {
                $manager = User::find($swap->manager_id);
                if ($manager) {
                    $manager->notify(new ShiftSwapNotification($swap, 'cancelled'));
                }
            }

            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function validateWorkerNoAbsenceConflicts(string $workerId, array $dateStrings, string $action): void
    {
        if (empty($dateStrings)) {
            return;
        }

        foreach (array_unique($dateStrings) as $dateStr) {
            $formattedDate = Carbon::parse($dateStr)->format('d M Y');

            $hasLeave = LeaveRequest::where('worker_id', $workerId)
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->exists();

            if ($hasLeave) {
                throw new \Exception("Tidak dapat {$action} pada tanggal {$formattedDate} karena Anda memiliki pengajuan cuti pending/disetujui pada tanggal tersebut.");
            }

            $hasBusinessTrip = BusinessTrip::where('worker_id', $workerId)
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->exists();

            if ($hasBusinessTrip) {
                throw new \Exception("Tidak dapat {$action} pada tanggal {$formattedDate} karena Anda memiliki pengajuan perjalanan dinas pending/disetujui pada tanggal tersebut.");
            }
        }
    }

    private function notifyFinalApprovers(ShiftSwapRequest $swap): void
    {
        $swap->loadMissing(['requester.department']);
        $departmentId = $swap->requester?->department_id;

        $approvers = User::query()
            ->where(function ($query) use ($departmentId) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['HR', 'Super Admin']);
                });

                if ($departmentId) {
                    $query->orWhere(function ($managerQuery) use ($departmentId) {
                        $managerQuery->whereHas('roles', function ($roleQuery) {
                            $roleQuery->where('name', 'Manager');
                        })->whereHas('worker', function ($workerQuery) use ($departmentId) {
                            $workerQuery->where('department_id', $departmentId);
                        });
                    });
                }
            })
            ->get()
            ->unique('id');

        foreach ($approvers as $approver) {
            $approver->notify(new ShiftSwapNotification($swap, 'manager_approval_needed'));
        }
    }

    private function getSwapDates(ShiftSwapRequest $swap): array
    {
        $dates = [];

        switch ($swap->swap_type ?? 'single_date') {
            case 'single_date':
                if ($swap->swap_date) {
                    $dates = [Carbon::parse($swap->swap_date)->toDateString()];
                }
                break;

            case 'date_range':
                if ($swap->swap_start_date && $swap->swap_end_date) {
                    $start = Carbon::parse($swap->swap_start_date);
                    $end = Carbon::parse($swap->swap_end_date);
                    while ($start->lte($end)) {
                        $dates[] = $start->toDateString();
                        $start->addDay();
                    }
                }
                break;

            case 'recurring':
                $dates = array_filter(array_map(function ($date) {
                    return $date ? Carbon::parse($date)->toDateString() : null;
                }, $swap->swap_dates ?? []));
                break;
        }

        if (empty($dates) && !empty($swap->metadata['swap_date'])) {
            $dates = [Carbon::parse($swap->metadata['swap_date'])->toDateString()];
        }

        if (empty($dates) && !empty($swap->swap_dates) && is_array($swap->swap_dates)) {
            $firstDate = $swap->swap_dates[0] ?? null;
            if ($firstDate) {
                $dates = [Carbon::parse($firstDate)->toDateString()];
            }
        }

        return $dates;
    }

    private function extractSwapDatesFromArray(array $data): array
    {
        $swapDates = [];
        $swapType = $data['swap_type'] ?? 'single_date';

        switch ($swapType) {
            case 'single_date':
                $singleDate = $data['swap_date'] ?? $data['swap_start_date'] ?? null;
                if ($singleDate) {
                    $swapDates[] = Carbon::parse($singleDate)->format('Y-m-d');
                }
                break;

            case 'date_range':
                if (!empty($data['swap_start_date']) && !empty($data['swap_end_date'])) {
                    $start = Carbon::parse($data['swap_start_date']);
                    $end = Carbon::parse($data['swap_end_date']);
                    while ($start->lte($end)) {
                        $swapDates[] = $start->format('Y-m-d');
                        $start->addDay();
                    }
                }
                break;

            case 'recurring':
                foreach (($data['swap_dates'] ?? []) as $date) {
                    if ($date) {
                        $swapDates[] = Carbon::parse($date)->format('Y-m-d');
                    }
                }
                break;
        }

        return $swapDates;
    }
}
