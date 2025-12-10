<?php

namespace App\Repositories\Master;

use App\Models\Position;
use App\Repositories\Contracts\Master\PositionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PositionRepository implements PositionRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Position $model
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->withCount('workers')
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

    public function withFileRequirements(string $id)
    {
        return $this->model
            ->with('fileRequirments.documentType')
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $position = $this->findById($id);
        return $position->update($data);
    }

    public function delete(string $id): bool
    {
        $position = $this->findById($id);
        return $position->delete();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->withCount('workers')
            ->orderBy('name')
            ->paginate($perPage);
    }
}
