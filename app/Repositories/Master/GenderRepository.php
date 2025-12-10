<?php

namespace App\Repositories\Master;

use App\Models\Gender;
use App\Repositories\Contracts\Master\GenderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GenderRepository implements GenderRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Gender $model
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
        $gender = $this->findById($id);
        return $gender->update($data);
    }

    public function delete(string $id): bool
    {
        $gender = $this->findById($id);
        return $gender->delete();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('name', 'like', "%{$keyword}%")
            ->orderBy('name')
            ->paginate($perPage);
    }
}
