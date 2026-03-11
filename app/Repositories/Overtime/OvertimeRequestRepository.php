<?php
// filepath: app/Repositories/Overtime/OvertimeRequestRepository.php

namespace App\Repositories\Overtime;

use App\DTOs\OvertimeRequestDTO;
use App\Models\OvertimeRequest;
use App\Repositories\Contracts\Overtime\OvertimeRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OvertimeRequestRepository implements OvertimeRequestRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected OvertimeRequest $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with([
            'worker.shiftOverrides.shift',
            'worker.activeWorkerShift.shift',
            'approver'
        ]);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $dateFrom = $filters['date_from'] ?? $filters['start_date'] ?? null;
        $dateTo = $filters['date_to'] ?? $filters['end_date'] ?? null;

        if (!empty($dateFrom)) {
            $query->where('overtime_date', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->where('overtime_date', '<=', $dateTo);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('overtime_date', $filters['year']);
        }

        // Department filter (for Manager access control)
        if (!empty($filters['department_id'])) {
            $query->whereHas('worker', function($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        // Advanced search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('overtime_date', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('total_hours', 'like', "%{$search}%")
                  ->orWhereHas('worker', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderByDesc('created_at')
            ->orderByDesc('overtime_date')
            ->paginate($filters['per_page'] ?? 15)
            ->appends($filters);
    }

    public function getById(string $id): ?object
    {
        return $this->model->with([
            'worker.shiftOverrides.shift',
            'worker.activeWorkerShift.shift',
            'approver'
        ])->find($id);
    }

    public function getByWorkerId(string $workerId, array $filters = []): Collection
    {
        $query = $this->model->with([
            'worker.shiftOverrides.shift',
            'worker.activeWorkerShift.shift'
        ])->where('worker_id', $workerId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('overtime_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('overtime_date', $filters['year']);
        }

        return $query->orderByDesc('created_at')->orderByDesc('overtime_date')->get();
    }

    public function getPendingRequests(): Collection
    {
        return $this->model->where('status', 'pending')
            ->with([
                'worker.shiftOverrides.shift',
                'worker.activeWorkerShift.shift'
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('overtime_date')
            ->get();
    }

    public function create(OvertimeRequestDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, OvertimeRequestDTO $dto): object
    {
        $overtime = $this->model->findOrFail($id);
        $overtime->update($dto->toArray());
        return $overtime->fresh();
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function approve(string $id, string $approvedBy): object
    {
        $overtime = $this->model->findOrFail($id);
        $overtime->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
        return $overtime->fresh();
    }

    public function reject(string $id, string $approvedBy, string $reason): object
    {
        $overtime = $this->model->findOrFail($id);
        $overtime->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
        return $overtime->fresh();
    }
}
