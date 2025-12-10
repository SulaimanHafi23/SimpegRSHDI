<?php

namespace App\Repositories\Master;

use App\Models\ShiftPattern;
use App\Repositories\Contracts\Master\ShiftPatternRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ShiftPatternRepository implements ShiftPatternRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly ShiftPattern $model
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->withCount('workerShiftSchedules')
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

    public function findByType(string $type): Collection
    {
        return $this->model
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $shiftPattern = $this->findById($id);
        return $shiftPattern->update($data);
    }

    public function delete(string $id): bool
    {
        $shiftPattern = $this->findById($id);
        return $shiftPattern->delete();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('type', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->withCount('workerShiftSchedules')
            ->orderBy('name')
            ->paginate($perPage);
    }
}
