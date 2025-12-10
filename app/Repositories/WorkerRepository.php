<?php

namespace App\Repositories;

use App\Models\Worker;
use App\Repositories\Contracts\WorkerRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class WorkerRepository implements WorkerRepositoryInterface
{
    public function __construct(
        private readonly Worker $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['gender', 'religion', 'position', 'user']);

        // Apply filters
        if (!empty($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model
            ->with(['gender', 'religion', 'position'])
            ->orderBy('name')
            ->get();
    }

    public function active(): Collection
    {
        return $this->model
            ->with(['gender', 'religion', 'position'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id)
    {
        return $this->model
            ->with([
                'gender',
                'religion',
                'position',
                'user.roles',
                'absents' => fn($q) => $q->latest()->limit(10),
                'berkas',
                'salary'
            ])
            ->findOrFail($id);
    }

    public function findByNik(string $nik)
    {
        return $this->model
            ->where('nik', $nik)
            ->first();
    }

    public function findByEmail(string $email)
    {
        return $this->model
            ->where('email', $email)
            ->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $worker = $this->model->findOrFail($id);
        return $worker->update($data);
    }

    public function delete(string $id): bool
    {
        $worker = $this->model->findOrFail($id);
        return $worker->delete();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['gender', 'religion', 'position', 'user'])
            ->where(function ($query) use ($keyword) {
                $query->where('nik', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhereHas('position', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function getByPosition(string $positionId): Collection
    {
        return $this->model
            ->where('position_id', $positionId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model
            ->where('status', $status)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getBirthdaysThisMonth(): Collection
    {
        $currentMonth = Carbon::now()->month;

        return $this->model
            ->with(['position'])
            ->where('is_active', true)
            ->whereMonth('date_of_birth', $currentMonth)
            ->orderByRaw('DAY(date_of_birth)')
            ->get();
    }

    public function getContractExpiring(int $days = 30): Collection
    {
        // This would need a contract_end_date column
        // For now, return empty collection
        return collect([]);
    }
}
