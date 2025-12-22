<?php

namespace App\Repositories\Master;

use App\DTOs\Master\LocationDTO;
use App\Models\Location;
use App\Repositories\Contracts\Master\LocationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LocationRepository implements LocationRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected Location $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query()->withCount('attendances');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('address', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('attendances')
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
        return $this->model->with(['attendances'])
            ->withCount('attendances')
            ->find($id);
    }

    public function getById(string $id): ?object
    {
        return $this->findById($id);
    }

    public function getByName(string $name): ?object
    {
        return $this->model->where('name', $name)->first();
    }

    public function create(LocationDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, LocationDTO $dto): object
    {
        $location = $this->model->findOrFail($id);
        $location->update($dto->toArray());
        return $location->fresh();
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function toggleStatus(string $id): object
    {
        $location = $this->model->findOrFail($id);
        $location->update(['is_active' => !$location->is_active]);
        return $location->fresh();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('attendances')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            })
            ->latest()
            ->paginate($perPage);
    }
}
