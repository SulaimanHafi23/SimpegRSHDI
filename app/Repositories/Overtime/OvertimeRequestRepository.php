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
        $query = $this->model->with(['worker', 'approver']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('overtime_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('overtime_date', '<=', $filters['date_to']);
        }

        return $query->latest('overtime_date')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(string $id): ?object
    {
        return $this->model->with(['worker', 'approver'])->find($id);
    }

    public function getByWorkerId(string $workerId, array $filters = []): Collection
    {
        $query = $this->model->where('worker_id', $workerId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('overtime_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('overtime_date', $filters['year']);
        }

        return $query->latest('overtime_date')->get();
    }

    public function getPendingRequests(): Collection
    {
        return $this->model->where('status', 'pending')
            ->with(['worker'])
            ->latest('overtime_date')
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
