<?php
// filepath: app/Repositories/Overtime/OvertimeRepository.php

namespace App\Repositories\Overtime;

use App\Models\Overtime;
use App\Repositories\Contracts\Overtime\OvertimeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OvertimeRepository implements OvertimeRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Overtime $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['worker.position', 'approver']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('date', '<=', $filters['end_date']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('date', $filters['year']);
        }

        return $query
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['worker.position', 'approver'])
            ->findOrFail($id);
    }

    public function getByWorker(string $workerId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->where('worker_id', $workerId);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->model
            ->with(['worker.position'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $overtime = $this->findById($id);
        return $overtime->update($data);
    }

    public function delete(string $id): bool
    {
        $overtime = $this->findById($id);
        return $overtime->delete();
    }

    public function approve(string $id, string $userId): bool
    {
        return $this->update($id, [
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject(string $id, string $userId, string $reason): bool
    {
        return $this->update($id, [
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
