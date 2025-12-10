<?php

namespace App\Repositories\Master;

use App\Models\Location;
use App\Repositories\Contracts\Master\LocationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LocationRepository implements LocationRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Location $model
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->withCount('absents')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model
            ->orderBy('name')
            ->get();
    }

    public function active(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $location = $this->findById($id);
        return $location->update($data);
    }

    public function delete(string $id): bool
    {
        $location = $this->findById($id);
        return $location->delete();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('address', 'like', "%{$keyword}%")
            ->withCount('absents')
            ->orderBy('name')
            ->paginate($perPage);
    }
}
