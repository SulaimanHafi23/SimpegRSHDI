<?php

namespace App\Repositories\Master;

use App\Models\Religion;
use App\Repositories\Contracts\Master\ReligionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReligionRepository implements ReligionRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Religion $model
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model
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
        $religion = $this->findById($id);
        return $religion->update($data);
    }

    public function delete(string $id): bool
    {
        $religion = $this->findById($id);
        return $religion->delete();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('name', 'like', "%{$keyword}%")
            ->orderBy('name')
            ->paginate($perPage);
    }
}
