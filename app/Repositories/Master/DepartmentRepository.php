<?php

namespace App\Repositories\Master;

use App\DTOs\Master\DepartmentDTO;
use App\Models\Department;
use App\Repositories\Contracts\Master\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function __construct(
        protected Department $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->withCount('workers');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('workers')
            ->latest()
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model->orderBy('name')
            ->get();
    }

    public function active(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id): ?object
    {
        return $this->model->with('workers')
            ->find($id);
    }

    public function getByCode(string $code): ?object
    {
        return $this->model->where('code', $code)->first();
    }

    public function getByName(string $name): ?object
    {
        return $this->model->where('name', $name)->first();
    }

    public function create(DepartmentDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, DepartmentDTO $dto): object
    {
        $department = $this->model->findOrFail($id);
        $department->update($dto->toArray());
        return $department->fresh(['parent', 'manager', 'workers']);
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function toggleStatus(string $id): object
    {
        $department = $this->model->findOrFail($id);
        $department->update(['is_active' => !$department->is_active]);
        return $department->fresh(['parent', 'manager']);
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['parent', 'manager'])
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function getHierarchy(): Collection
    {
        return $this->model->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->with('children')->where('is_active', true);
            }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getChildren(string $parentId): Collection
    {
        return $this->model->where('parent_id', $parentId)
            ->with(['manager', 'workers'])
            ->orderBy('name')
            ->get();
    }
}
