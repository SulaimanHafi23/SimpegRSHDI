<?php

namespace App\Repositories;

use App\Models\LeaveRequest;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class LeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    public function __construct(
        private readonly LeaveRequest $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['worker.position', 'leaveType', 'approver']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('start_date', $filters['year']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('start_date', $filters['month']);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['worker.position', 'leaveType', 'approver'])
            ->findOrFail($id);
    }

    public function getByWorker(string $workerId, ?string $year = null): Collection
    {
        $query = $this->model
            ->with(['leaveType'])
            ->where('worker_id', $workerId);

        if ($year) {
            $query->whereYear('start_date', $year);
        }

        return $query
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->model
            ->with(['worker.position', 'leaveType'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getApproved(): Collection
    {
        return $this->model
            ->with(['worker.position', 'leaveType'])
            ->where('status', 'approved')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $leaveRequest = $this->findById($id);
        return $leaveRequest->update($data);
    }

    public function delete(string $id): bool
    {
        $leaveRequest = $this->findById($id);
        return $leaveRequest->delete();
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

    public function checkOverlap(string $workerId, string $startDate, string $endDate, ?string $excludeId = null): bool
    {
        $query = $this->model
            ->where('worker_id', $workerId)
            ->where('status', '!=', 'rejected')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
