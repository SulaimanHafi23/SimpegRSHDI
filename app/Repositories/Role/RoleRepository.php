<?php

namespace App\Repositories\Role;

use App\Repositories\Contracts\Role\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Role $model
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->withCount('permissions', 'users')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function getAll(): Collection
    {
        return $this->model
            ->with('permissions')
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id)
    {
        return $this->model
            ->with('permissions')
            ->findOrFail($id);
    }

    public function findByName(string $name)
    {
        return $this->model
            ->with('permissions')
            ->where('name', $name)
            ->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $role = $this->findById($id);
        return $role->update($data);
    }

    public function delete(string $id): bool
    {
        $role = $this->findById($id);
        return $role->delete();
    }

    public function syncPermissions(string $id, array $permissions): bool
    {
        $role = $this->findById($id);
        $role->syncPermissions($permissions);
        return true;
    }

    public function getPermissions(string $id)
    {
        $role = $this->findById($id);
        return $role->permissions;
    }
}
