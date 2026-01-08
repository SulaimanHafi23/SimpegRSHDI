<?php
// filepath: app/Repositories/User/UserRepository.php

namespace App\Repositories\User;

use App\DTOs\UserDTO;
use App\Models\User;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['worker', 'roles']);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('username', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function getById(string $id): ?object
    {
        return $this->model->with(['worker', 'roles.permissions'])->find($id);
    }

    public function getByUsername(string $username): ?object
    {
        return $this->model->where('username', $username)
            ->with(['worker', 'roles.permissions'])
            ->first();
    }

    public function getByEmail(string $email): ?object
    {
        return $this->model->where('email', $email)->first();
    }

    public function getByWorkerId(string $workerId): ?object
    {
        return $this->model->where('worker_id', $workerId)->first();
    }

    public function create(UserDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, UserDTO $dto): object
    {
        $user = $this->model->findOrFail($id);
        $user->update($dto->toArray());
        return $user->fresh();
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function updatePassword(string $id, string $hashedPassword): object
    {
        $user = $this->model->findOrFail($id);
        $user->update(['password' => $hashedPassword]);
        return $user->fresh();
    }

    public function updateLastLogin(string $id): object
    {
        $user = $this->model->findOrFail($id);
        $user->update(['last_login' => now()]);
        return $user->fresh();
    }

    public function activate(string $id): object
    {
        $user = $this->model->findOrFail($id);
        $user->update(['is_active' => true]);
        return $user->fresh();
    }

    public function deactivate(string $id): object
    {
        $user = $this->model->findOrFail($id);
        $user->update(['is_active' => false]);
        return $user->fresh();
    }

    public function assignRoles(string $id, array $roles): object
    {
        $user = $this->model->findOrFail($id);
        $user->assignRole($roles);
        return $user->fresh(['roles']);
    }

    public function syncRoles(string $id, array $roles): object
    {
        $user = $this->model->findOrFail($id);
        $user->syncRoles($roles);
        return $user->fresh(['roles']);
    }

    public function removeRole(string $id, string $role): object
    {
        $user = $this->model->findOrFail($id);
        $user->removeRole($role);
        return $user->fresh(['roles']);
    }
}