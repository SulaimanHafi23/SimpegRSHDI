<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ShiftOverride;
use App\Models\ShiftSwapAuditLog;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use App\Models\Worker;
use App\Notifications\ShiftSwapNotification;
use App\Traits\DepartmentFilterable;
use App\Exports\ShiftSwapExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftSwapApprovalController extends Controller
{
    use DepartmentFilterable;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:shift-swap.approve');
    }

    /**
     * List pending approvals for manager
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        try {
            $filters = [
                // Default empty string means "show all statuses" when user has not chosen a specific filter.
                'status' => $request->input('status', ''),
                'requester_id' => $request->input('requester_id'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'per_page' => $request->input('per_page', 15),
            ];

            $items = $this->listPendingApprovalsForManager($user->id, $filters);

            // Get department filter for statistics and workers
            $departmentId = $this->getManagerDepartmentFilter();

            // Get statistics filtered by department for Manager
            $statsQuery = ShiftSwapRequest::query();
            if ($departmentId) {
                $statsQuery->where(function($q) use ($departmentId) {
                    $q->whereHas('requester', fn($r) => $r->where('department_id', $departmentId))
                      ->orWhereHas('targetWorker', fn($r) => $r->where('department_id', $departmentId));
                });
            }
            $statistics = [
                'total' => (clone $statsQuery)->count(),
                'awaiting_approval' => (clone $statsQuery)->where('status', 'awaiting_approval')->count(),
                'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
                'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
                'executed' => (clone $statsQuery)->whereNotNull('executed_at')->count(),
            ];

            // Get workers filtered by department for Manager
            if ($departmentId) {
                $workers = Worker::where('department_id', $departmentId)->orderBy('name')->get();
            } else {
                $workers = Worker::orderBy('name')->get();
            }

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
        $swap = ShiftSwapRequest::with([
            'requester.user',
            'requester.department',
            'targetWorker.user',
            'targetWorker.department',
            'requesterShift.shift',
            'targetShift.shift',
            'manager',
            'executedBy',
            'auditLogs.user',
        ])->findOrFail($id);

        // Department restriction applies only for manager-scoped users.
        $departmentId = $this->getManagerDepartmentFilter();
        if ($departmentId) {
            $requesterDept = $swap->requester->department_id ?? null;
            $targetDept = $swap->targetWorker->department_id ?? null;
            if ((string) $requesterDept !== (string) $departmentId && (string) $targetDept !== (string) $departmentId) {
                abort(403, 'Unauthorized');
            }
        }

        // Enrich with effective shifts (considering ShiftOverride)
        $this->enrichWithEffectiveShifts(collect([$swap]));

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

        $user = Auth::user();

        try {
            $this->approveByManager($id, $user->id, $request->input('notes'));
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

        $user = Auth::user();

        try {
            $this->rejectByManager($id, $user->id, $request->input('reason'));
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
        $user = Auth::user();

        try {
            $this->executeSwap($id, $user->id);
            return redirect()->route('manager.shift-swap-approvals.index')
                ->with('success', 'Pertukaran shift berhasil dieksekusi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengeksekusi pertukaran: ' . $e->getMessage());
        }
    }

    /**
     * Revert an executed swap
     */
    public function revert(Request $request, string $id)
    {
        $user = Auth::user();

        try {
            $reason = $request->input('reason', '');
            $this->revertSwap($id, $user->id, $reason);
            return redirect()->route('manager.shift-swap-approvals.index')
                ->with('success', 'Pertukaran shift berhasil di-revert.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal me-revert pertukaran: ' . $e->getMessage());
        }
    }

    /**
     * Export shift swap data (PDF, Excel, CSV)
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $format = $request->input('format', 'pdf');

        $filters = [
            'status' => $request->input('status', ''),
            'requester_id' => $request->input('requester_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'per_page' => 10000,
        ];

        $items = $this->listPendingApprovalsForManager($user->id, $filters);
        $swaps = collect($items->items());

        if ($format === 'excel') {
            return Excel::download(
                new ShiftSwapExport($swaps),
                'tukar-shift-approvals-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        if ($format === 'csv') {
            return Excel::download(
                new ShiftSwapExport($swaps),
                'tukar-shift-approvals-' . now()->format('Y-m-d') . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        }

        // PDF
        $pdf = Pdf::loadView('exports.shift-swap-pdf', [
            'swaps' => $swaps,
            'dateFrom' => $request->date_from ? \Carbon\Carbon::parse($request->date_from)->translatedFormat('d M Y') : null,
            'dateTo' => $request->date_to ? \Carbon\Carbon::parse($request->date_to)->translatedFormat('d M Y') : null,
            'status' => $request->status,
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Tukar_Shift_Approvals_' . now()->format('YmdHis') . '.pdf');
    }

    private function listPendingApprovalsForManager(string $managerId, array $filters = [])
    {
        $manager = User::findOrFail($managerId);
        $worker = $manager->worker;

        $query = ShiftSwapRequest::with(['requester', 'targetWorker', 'requesterShift.shift', 'targetShift.shift']);

        $departmentId = null;
        if ($worker && $manager->can('dashboard.manager') && !$manager->can('dashboard.admin') && !$manager->can('dashboard.hr')) {
            $departmentId = $worker->department_id;
        }

        if ($departmentId) {
            $query->where(function ($q) use ($worker) {
                $q->whereHas('requester', function ($sub) use ($worker) {
                    $sub->where('department_id', $worker->department_id);
                })
                ->orWhereHas('targetWorker', function ($sub) use ($worker) {
                    $sub->where('department_id', $worker->department_id);
                });
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        } elseif (!isset($filters['status'])) {
            $query->where('status', 'awaiting_approval')
                ->where('requires_manager_approval', true);
        }

        if (!empty($filters['requester_id'])) {
            $query->where('requester_id', $filters['requester_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('requested_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('requested_at', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;
        $result = $query->orderBy('requested_at', 'desc')->paginate($perPage);
        $this->enrichWithEffectiveShifts($result->getCollection());

        return $result;
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

    private function approveByManager(string $swapId, string $managerId, ?string $notes = null): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);
        $approver = User::findOrFail($managerId);

        if (!$approver->can('shift-swap.approve')) {
            throw new \Exception('Anda tidak berhak menyetujui permintaan tukar shift ini.');
        }

        if ($swap->status !== 'awaiting_approval') {
            throw new \Exception('Swap request tidak dalam status awaiting approval.');
        }

        if (!$swap->requires_manager_approval) {
            throw new \Exception('Swap request ini tidak memerlukan persetujuan manager/HR.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'approved';
            $swap->manager_id = $managerId;
            $swap->manager_approved_at = Carbon::now();

            if ($notes) {
                $metadata = $swap->metadata ?? [];
                $metadata['manager_notes'] = $notes;
                $swap->metadata = $metadata;
            }

            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'approved_by_manager',
                newStatus: 'approved',
                userId: $managerId,
                oldStatus: $oldStatus,
                notes: $notes ?? 'Manager approved the swap request',
                metadata: [
                    'manager_id' => $managerId,
                    'approved_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            Log::info('Shift swap approved by manager', [
                'swap_id' => $swapId,
                'manager_id' => $managerId,
                'notes' => $notes,
            ]);

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'approved_by_manager', $notes));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'approved_by_manager', $notes));
            }

            DB::commit();

            return $this->executeSwap($swapId, $managerId);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve shift swap', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function rejectByManager(string $swapId, string $managerId, string $reason): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::findOrFail($swapId);
        $approver = User::findOrFail($managerId);

        if (!$approver->can('shift-swap.approve')) {
            throw new \Exception('Anda tidak berhak menolak permintaan tukar shift ini.');
        }

        if ($swap->status !== 'awaiting_approval') {
            throw new \Exception('Swap request tidak dalam status awaiting approval.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $swap->status;
            $swap->status = 'rejected';
            $swap->manager_id = $managerId;

            $metadata = $swap->metadata ?? [];
            $metadata['rejection_reason'] = $reason;
            $metadata['rejected_by'] = 'manager';
            $metadata['rejected_at'] = Carbon::now()->toDateTimeString();
            $swap->metadata = $metadata;

            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'rejected_by_manager',
                newStatus: 'rejected',
                userId: $managerId,
                oldStatus: $oldStatus,
                notes: $reason,
                metadata: [
                    'manager_id' => $managerId,
                    'rejected_by' => 'manager',
                    'rejected_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            Log::info('Shift swap rejected by manager', [
                'swap_id' => $swapId,
                'manager_id' => $managerId,
                'reason' => $reason,
            ]);

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'rejected_by_manager', $reason));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'rejected_by_manager', $reason));
            }

            DB::commit();
            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject shift swap', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function executeSwap(string $swapId, string $executedByUserId): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::with(['requester', 'targetWorker', 'requesterShift', 'targetShift'])
            ->findOrFail($swapId);

        if (!in_array($swap->status, ['approved', 'accepted'])) {
            throw new \Exception('Swap hanya dapat dieksekusi jika sudah approved/accepted.');
        }

        if (!$swap->target_shift_id) {
            throw new \Exception('Target shift tidak ditentukan, tidak dapat mengeksekusi swap.');
        }

        DB::beginTransaction();
        try {
            $requesterShift = $swap->requesterShift;
            $targetShift = $swap->targetShift;

            $dates = $this->getSwapDates($swap);
            if (empty($dates)) {
                throw new \Exception('Tidak ada tanggal yang valid untuk tukar shift.');
            }

            foreach ($dates as $date) {
                if (!$date) {
                    continue;
                }

                $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;

                ShiftOverride::updateOrCreate(
                    [
                        'worker_id' => $swap->requester_id,
                        'override_date' => $dateStr,
                    ],
                    [
                        'shift_id' => $targetShift->shift_id,
                        'reason' => 'Tukar shift dengan ' . ($swap->targetWorker->full_name ?? 'pegawai lain') . ': ' . ($swap->reason ?? ''),
                        'created_by' => $executedByUserId,
                        'shift_swap_request_id' => $swap->id,
                    ]
                );

                if ($swap->target_worker_id) {
                    ShiftOverride::updateOrCreate(
                        [
                            'worker_id' => $swap->target_worker_id,
                            'override_date' => $dateStr,
                        ],
                        [
                            'shift_id' => $requesterShift->shift_id,
                            'reason' => 'Tukar shift dengan ' . ($swap->requester->full_name ?? 'pegawai lain') . ': ' . ($swap->reason ?? ''),
                            'created_by' => $executedByUserId,
                            'shift_swap_request_id' => $swap->id,
                        ]
                    );
                }
            }

            $oldStatus = $swap->status;
            $swap->status = 'executed';
            $swap->executed_at = Carbon::now();
            $swap->executed_by = $executedByUserId;
            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'executed',
                newStatus: 'executed',
                userId: $executedByUserId,
                oldStatus: $oldStatus,
                notes: 'Shift swap executed successfully (date-specific overrides created)',
                metadata: [
                    'requester_id' => $swap->requester_id,
                    'requester_original_shift' => $requesterShift->shift_id,
                    'requester_new_shift' => $targetShift->shift_id,
                    'target_id' => $swap->target_worker_id,
                    'target_original_shift' => $targetShift->shift_id,
                    'target_new_shift' => $requesterShift->shift_id,
                    'swap_dates' => $dates,
                    'executed_by' => $executedByUserId,
                    'executed_at' => Carbon::now()->toDateTimeString(),
                ]
            );

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'executed'));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'executed'));
            }

            DB::commit();

            Log::info('Shift swap executed successfully', [
                'swap_id' => $swapId,
                'requester_id' => $swap->requester_id,
                'target_id' => $swap->target_worker_id,
                'executed_by' => $executedByUserId,
                'dates' => $dates,
            ]);

            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to execute shift swap', [
                'swap_id' => $swapId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function revertSwap(string $swapId, string $revertedByUserId, string $reason = ''): ShiftSwapRequest
    {
        $swap = ShiftSwapRequest::with(['requester', 'targetWorker'])
            ->findOrFail($swapId);

        if ($swap->status !== 'executed') {
            throw new \Exception('Hanya swap yang sudah dieksekusi yang dapat di-revert.');
        }

        DB::beginTransaction();
        try {
            $deletedCount = ShiftOverride::where('shift_swap_request_id', $swap->id)->delete();

            $oldStatus = $swap->status;
            $swap->status = 'reverted';
            $swap->save();

            ShiftSwapAuditLog::log(
                shiftSwapRequestId: $swap->id,
                action: 'reverted',
                newStatus: 'reverted',
                userId: $revertedByUserId,
                oldStatus: $oldStatus,
                notes: 'Shift swap reverted - ' . ($reason ?: 'overrides removed') . " ({$deletedCount} overrides deleted)",
                metadata: [
                    'reverted_by' => $revertedByUserId,
                    'reverted_at' => Carbon::now()->toDateTimeString(),
                    'overrides_deleted' => $deletedCount,
                    'reason' => $reason,
                ]
            );

            if ($swap->requester && $swap->requester->user) {
                $swap->requester->user->notify(new ShiftSwapNotification($swap, 'reverted'));
            }
            if ($swap->targetWorker && $swap->targetWorker->user) {
                $swap->targetWorker->user->notify(new ShiftSwapNotification($swap, 'reverted'));
            }

            DB::commit();

            Log::info('Shift swap reverted successfully', [
                'swap_id' => $swapId,
                'reverted_by' => $revertedByUserId,
                'overrides_deleted' => $deletedCount,
            ]);

            return $swap;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to revert shift swap', ['swap_id' => $swapId, 'error' => $e->getMessage()]);
            throw $e;
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
                $dates = array_filter(array_map(function ($d) {
                    return $d ? Carbon::parse($d)->toDateString() : null;
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
}
