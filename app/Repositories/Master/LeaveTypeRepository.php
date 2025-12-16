<?php

namespace App\Repositories\Master;

use App\DTOs\Master\LeaveTypeDTO;
use App\Models\LeaveType;
use App\Repositories\Contracts\Master\LeaveTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveTypeRepository implements LeaveTypeRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected LeaveType $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query()->withCount('leaveRequests');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_paid'])) {
            $query->where('is_paid', $filters['is_paid']);
        }

        if (isset($filters['requires_approval'])) {
            $query->where('requires_approval', $filters['requires_approval']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('code', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('leaveRequests')
            ->latest()
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    public function active(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id): ?object
    {
        return $this->model->with(['leaveRequests'])
            ->withCount('leaveRequests')
            ->find($id);
    }

    public function getByName(string $name): ?object
    {
        return $this->model->where('name', $name)->first();
    }

    public function getByCode(string $code): ?object
    {
        return $this->model->where('code', $code)->first();
    }

    public function create(LeaveTypeDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, LeaveTypeDTO $dto): object
    {
        $leaveType = $this->model->findOrFail($id);
        $leaveType->update($dto->toArray());
        return $leaveType->fresh(['leaveRequests']);
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function toggleStatus(string $id): object
    {
        $leaveType = $this->model->findOrFail($id);
        $leaveType->update(['is_active' => !$leaveType->is_active]);
        return $leaveType->fresh();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('leaveRequests')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function getPaidLeaveTypes(): Collection
    {
        return $this->model->where('is_paid', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getRequiringApproval(): Collection
    {
        return $this->model->where('requires_approval', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
