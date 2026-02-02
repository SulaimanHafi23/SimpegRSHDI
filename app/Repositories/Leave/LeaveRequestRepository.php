<?php

namespace App\Repositories\Leave;

use App\DTOs\LeaveRequestDTO;
use App\Models\LeaveRequest;
use App\Repositories\Contracts\Leave\LeaveRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    public function __construct(
        protected LeaveRequest $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['worker', 'leaveType', 'approver']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('end_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('start_date', $filters['year']);
        }

        // Advanced search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('start_date', 'like', "%{$search}%")
                  ->orWhere('end_date', 'like', "%{$search}%")
                  ->orWhereHas('leaveType', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('worker', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->latest('start_date')
            ->paginate($filters['per_page'] ?? 15)
            ->appends($filters);
    }

    public function getById(string $id): ?object
    {
        return $this->model->with(['worker', 'leaveType', 'approver'])->find($id);
    }

    public function getByWorkerId(string $workerId, array $filters = []): Collection
    {
        $query = $this->model->where('worker_id', $workerId)
            ->with(['leaveType', 'approver']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('start_date', $filters['year']);
        }

        return $query->latest('start_date')->get();
    }

    public function getPendingRequests(): Collection
    {
        return $this->model->where('status', 'pending')
            ->with(['worker', 'leaveType'])
            ->latest('start_date')
            ->get();
    }

    /**
     * Get count of leave requests by status for a specific worker and year
     */
    public function countByStatus(string $workerId, int $year, ?string $status = null): int
    {
        $query = $this->model->where('worker_id', $workerId)
            ->whereYear('start_date', $year);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->count();
    }

    public function create(LeaveRequestDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, LeaveRequestDTO $dto): object
    {
        $leaveRequest = $this->model->findOrFail($id);
        $leaveRequest->update($dto->toArray());
        return $leaveRequest->fresh();
    }

    public function delete(string $id): bool
    {
        $leaveRequest = $this->model->findOrFail($id);

        // Delete attachment if exists
        if ($leaveRequest->attachment_path && \Storage::exists($leaveRequest->attachment_path)) {
            \Storage::delete($leaveRequest->attachment_path);
        }

        return $leaveRequest->delete();
    }

    public function approve(string $id, string $approvedBy): object
    {
        $leaveRequest = $this->model->findOrFail($id);
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
        return $leaveRequest->fresh();
    }

    public function reject(string $id, string $approvedBy, string $reason): object
    {
        $leaveRequest = $this->model->findOrFail($id);
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
        return $leaveRequest->fresh();
    }

    public function cancel(string $id): object
    {
        $leaveRequest = $this->model->findOrFail($id);
        $leaveRequest->update(['status' => 'cancelled']);
        return $leaveRequest->fresh();
    }

    public function getWorkerLeaveBalance(string $workerId, string $leaveTypeId, int $year): int
    {
        $leaveType = \App\Models\LeaveType::findOrFail($leaveTypeId);

        if (!$leaveType->max_days_per_year) {
            return 0; // Unlimited
        }

        $usedDays = $this->model->where('worker_id', $workerId)
            ->where('leave_type_id', $leaveTypeId)
            ->whereYear('start_date', $year)
            ->whereIn('status', ['approved', 'pending'])
            ->sum('total_days');

        return max(0, $leaveType->max_days_per_year - $usedDays);
    }
}
