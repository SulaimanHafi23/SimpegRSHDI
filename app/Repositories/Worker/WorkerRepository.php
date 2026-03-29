<?php

namespace App\Repositories\Worker;

use App\DTOs\WorkerDTO;
use App\Models\Worker;
use App\Repositories\Contracts\Worker\WorkerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkerRepository implements WorkerRepositoryInterface
{
    public function __construct(
        protected Worker $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['department', 'user.roles']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['employment_status'])) {
            $query->where('employment_status', $filters['employment_status']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $searchTerm . '%']);
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function getAllActive(): Collection
    {
        return $this->model->where('status', 'active')
            ->with(['department'])
            ->get();
    }

    public function getById(string $id): ?object
    {
        return $this->model->with([
            'department',
            'user',
            'activeWorkerShift.shift'
        ])->find($id);
    }

    public function getByNip(string $nip): ?object
    {
        return $this->model->where('nip', $nip)->first();
    }

    public function getByEmail(string $email): ?object
    {
        return $this->model->where('email', $email)->first();
    }

    public function getByDepartment(string $departmentId): Collection
    {
        return $this->model->where('department_id', $departmentId)
            ->where('status', 'active')
            ->with(['department'])
            ->get();
    }

    public function create(WorkerDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, WorkerDTO $dto): object
    {
        $worker = $this->model->findOrFail($id);
        $worker->update($dto->toArray());
        return $worker->fresh();
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function resign(string $id, string $resignDate): object
    {
        $worker = $this->model->findOrFail($id);
        $worker->update([
            'status' => 'resigned',
            'resign_date' => $resignDate,
        ]);
        return $worker->fresh();
    }

    public function restore(string $id): object
    {
        $worker = $this->model->withTrashed()->findOrFail($id);
        $worker->restore();
        return $worker->fresh();
    }

    public function updateStatus(string $id, string $status): object
    {
        $worker = $this->model->findOrFail($id);
        $worker->update(['status' => $status]);
        return $worker->fresh();
    }

    public function updatePhoto(string $id, string $photoUrl): object
    {
        $worker = $this->model->findOrFail($id);
        $worker->update(['photo_url' => $photoUrl]);
        return $worker->fresh();
    }
}
