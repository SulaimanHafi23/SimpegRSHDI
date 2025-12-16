<?php

namespace App\Repositories\Master;

use App\DTOs\Master\ReligionDTO;
use App\Models\Religion;
use App\Repositories\Contracts\Master\ReligionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReligionRepository implements ReligionRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected Religion $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query()->withCount('workers');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
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
        return $this->model->with(['workers'])
            ->withCount('workers')
            ->find($id);
    }

    public function getByName(string $name): ?object
    {
        return $this->model->where('name', $name)->first();
    }

    public function create(ReligionDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, ReligionDTO $dto): object
    {
        $religion = $this->model->findOrFail($id);
        $religion->update($dto->toArray());
        return $religion->fresh(['workers']);
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function toggleStatus(string $id): object
    {
        $religion = $this->model->findOrFail($id);
        $religion->update(['is_active' => !$religion->is_active]);
        return $religion->fresh();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('workers')
            ->where('name', 'like', "%{$keyword}%")
            ->latest()
            ->paginate($perPage);
    }
}
