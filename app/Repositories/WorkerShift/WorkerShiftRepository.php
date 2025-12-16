<?php
// filepath: app/Repositories/WorkerShift/WorkerShiftRepository.php

namespace App\Repositories\WorkerShift;

use App\DTOs\WorkerShiftDTO;
use App\Models\WorkerShift;
use App\Repositories\Contracts\WorkerShift\WorkerShiftRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkerShiftRepository implements WorkerShiftRepositoryInterface
{
    public function __construct(
        protected WorkerShift $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['worker', 'shift']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['pattern_type'])) {
            $query->where('pattern_type', $filters['pattern_type']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function getById(string $id): ?object
    {
        return $this->model->with(['worker', 'shift'])->find($id);
    }

    public function getByWorkerId(string $workerId): Collection
    {
        return $this->model->where('worker_id', $workerId)
            ->with(['shift'])
            ->latest()
            ->get();
    }

    public function getActiveByWorkerId(string $workerId): ?object
    {
        return $this->model->where('worker_id', $workerId)
            ->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->with(['shift'])
            ->first();
    }

    public function create(WorkerShiftDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, WorkerShiftDTO $dto): object
    {
        $workerShift = $this->model->findOrFail($id);
        $workerShift->update($dto->toArray());
        return $workerShift->fresh();
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function deactivateOldShifts(string $workerId): void
    {
        $this->model->where('worker_id', $workerId)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}