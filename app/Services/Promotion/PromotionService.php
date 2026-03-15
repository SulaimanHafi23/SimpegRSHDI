<?php

namespace App\Services\Promotion;

use App\Models\AuditLog;
use App\Models\PromotionHistory;
use App\Models\PromotionRequest;
use App\Models\Worker;
use App\Notifications\PromotionStatusNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = PromotionRequest::query()->with(['worker.department']);

        if (!empty($filters['search'])) {
            $query->whereHas('worker', fn ($q) =>
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('nip', 'like', '%' . $filters['search'] . '%')
            );
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        $query->orderByDesc('created_at');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getById(string $id): PromotionRequest
    {
        return PromotionRequest::with(['worker.department', 'reviewer'])->findOrFail($id);
    }

    public function create(array $data): PromotionRequest
    {
        $worker = Worker::findOrFail($data['worker_id']);

        $request = PromotionRequest::create([
            'worker_id'            => $worker->id,
            'promotion_type'       => $data['promotion_type'] ?? 'kenaikan_pangkat',
            'current_rank'         => $worker->rank,
            'current_rank_level'   => $worker->rank_level,
            'proposed_rank'        => $data['proposed_rank'],
            'proposed_rank_level'  => $data['proposed_rank_level'] ?? null,
            'current_base_salary'  => $worker->base_salary ?? 0,
            'proposed_base_salary' => $data['proposed_base_salary'],
            'effective_date'       => $data['effective_date'],
            'reason'               => $data['reason'] ?? null,
            'status'               => 'pending',
        ]);

        AuditLog::log(
            action: 'promotion_request_created',
            description: 'Pengajuan kenaikan pangkat untuk ' . $worker->name,
            auditable: $request,
            newValues: $request->toArray(),
        );

        return $request;
    }

    public function approve(PromotionRequest $request, ?string $notes = null): PromotionRequest
    {
        if ($request->status !== 'pending') {
            throw new \RuntimeException('Hanya pengajuan berstatus pending yang dapat disetujui.');
        }

        DB::transaction(function () use ($request, $notes) {
            $request->update([
                'status'        => 'approved',
                'reviewed_by'   => Auth::id(),
                'reviewed_at'   => now(),
                'rejection_reason' => null,
            ]);

            // Update worker data
            $worker = $request->worker;
            $worker->update([
                'rank'        => $request->proposed_rank,
                'rank_level'  => $request->proposed_rank_level,
                'base_salary' => $request->proposed_base_salary,
            ]);

            // Create history
            PromotionHistory::create([
                'worker_id'            => $worker->id,
                'promotion_request_id' => $request->id,
                'promotion_type'       => $request->promotion_type,
                'old_rank'             => $request->current_rank,
                'old_rank_level'       => $request->current_rank_level,
                'new_rank'             => $request->proposed_rank,
                'new_rank_level'       => $request->proposed_rank_level,
                'old_base_salary'      => $request->current_base_salary,
                'new_base_salary'      => $request->proposed_base_salary,
                'effective_date'       => $request->effective_date,
                'approved_by'          => Auth::id(),
                'notes'                => $notes,
            ]);

            // Notify worker
            if ($worker->user) {
                $worker->user->notify(new PromotionStatusNotification($request));
            }

            AuditLog::log(
                action: 'promotion_request_approved',
                description: 'Persetujuan kenaikan pangkat untuk ' . $worker->name,
                auditable: $request,
                newValues: ['status' => 'approved', 'new_rank' => $request->proposed_rank],
            );
        });

        return $request->fresh();
    }

    public function reject(PromotionRequest $request, string $reason): PromotionRequest
    {
        if ($request->status !== 'pending') {
            throw new \RuntimeException('Hanya pengajuan berstatus pending yang dapat ditolak.');
        }

        $request->update([
            'status'           => 'rejected',
            'reviewed_by'      => Auth::id(),
            'reviewed_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        // Notify worker
        if ($request->worker?->user) {
            $request->worker->user->notify(new PromotionStatusNotification($request));
        }

        AuditLog::log(
            action: 'promotion_request_rejected',
            description: 'Penolakan kenaikan pangkat untuk ' . optional($request->worker)->name,
            auditable: $request,
            newValues: ['status' => 'rejected', 'reason' => $reason],
        );

        return $request->fresh();
    }

    public function getWorkerHistory(string $workerId, array $filters = []): LengthAwarePaginator
    {
        $query = PromotionHistory::query()
            ->where('worker_id', $workerId)
            ->with(['promotionRequest', 'approvedBy'])
            ->orderByDesc('effective_date');

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function delete(PromotionRequest $request): void
    {
        if ($request->status !== 'pending') {
            throw new \RuntimeException('Hanya pengajuan berstatus pending yang dapat dihapus.');
        }

        $request->delete();
    }
}
