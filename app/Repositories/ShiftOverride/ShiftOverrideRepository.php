<?php

namespace App\Repositories\ShiftOverride;

use App\DTOs\ShiftOverrideDTO;
use App\Models\ShiftOverride;
use App\Repositories\Contracts\ShiftOverride\ShiftOverrideRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ShiftOverrideRepository implements ShiftOverrideRepositoryInterface
{
    public function __construct(
        protected ShiftOverride $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['worker', 'shift', 'creator']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('override_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('override_date', '<=', $filters['date_to']);
        }

        return $query->latest('override_date')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(string $id): ?object
    {
        return $this->model->with(['worker', 'shift', 'creator'])->find($id);
    }

    public function getByWorkerId(string $workerId): Collection
    {
        return $this->model->where('worker_id', $workerId)
            ->with(['shift'])
            ->latest('override_date')
            ->get();
    }

    public function getByDate(string $date): Collection
    {
        return $this->model->where('override_date', $date)
            ->with(['worker', 'shift'])
            ->get();
    }

    public function getByWorkerAndDate(string $workerId, string $date): ?object
    {
        return $this->model->where('worker_id', $workerId)
            ->where('override_date', $date)
            ->with(['shift'])
            ->first();
    }

    public function create(ShiftOverrideDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, ShiftOverrideDTO $dto): object
    {
        $shiftOverride = $this->model->findOrFail($id);
        $shiftOverride->update($dto->toArray());
        return $shiftOverride->fresh();
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
