<?php

namespace App\Repositories\Master;

use App\DTOs\Master\ShiftDTO;
use App\Models\Shift;
use App\Repositories\Contracts\Master\ShiftRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ShiftRepository implements ShiftRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected Shift $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query()->withCount('workerShifts');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_overnight'])) {
            $query->where('is_overnight', $filters['is_overnight']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('workerShifts')
            ->latest()
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model->orderBy('start_time')->get();
    }

    public function active(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('start_time')
            ->get();
    }

    public function findById(string $id): ?object
    {
        return $this->model->with(['workerShifts'])
            ->withCount('workerShifts')
            ->find($id);
    }

    public function getByName(string $name): ?object
    {
        return $this->model->where('name', $name)->first();
    }

    // ✅ PERBAIKAN: Terima ShiftDTO, bukan array
    public function create(ShiftDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    // ✅ PERBAIKAN: Terima ShiftDTO, bukan array
    public function update(string $id, ShiftDTO $dto): object
    {
        $shift = $this->model->findOrFail($id);
        $shift->update($dto->toArray());
        return $shift->fresh(['workerShifts']);
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function toggleStatus(string $id): object
    {
        $shift = $this->model->findOrFail($id);
        $shift->update(['is_active' => !$shift->is_active]);
        return $shift->fresh();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('workerShifts')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function getOvernightShifts(): Collection
    {
        return $this->model->where('is_overnight', true)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();
    }
}
