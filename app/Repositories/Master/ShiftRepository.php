<?php

namespace App\Repositories\Master;

use App\Models\Shift;
use App\Repositories\Contracts\Master\ShiftRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ShiftRepository implements ShiftRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Shift $model
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->withCount('schedules')
            ->orderBy('start_time')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model
            ->orderBy('start_time')
            ->get();
    }

    public function active(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('start_time')
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
        $shift = $this->findById($id);
        return $shift->update($data);
    }

    public function delete(string $id): bool
    {
        $shift = $this->findById($id);
        return $shift->delete();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->withCount('workerShiftSchedules')
            ->orderBy('start_time')
            ->paginate($perPage);
    }
}
