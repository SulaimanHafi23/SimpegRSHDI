<?php
// filepath: app/Repositories/UserRepository.php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['worker.position', 'roles']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('email', 'like', "%{$filters['search']}%")
                  ->orWhereHas('worker', function ($q2) use ($filters) {
                      $q2->where('name', 'like', "%{$filters['search']}%");
                  });
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAll(): Collection
    {
        return $this->model
            ->with(['worker.position', 'roles'])
            ->get();
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['worker.position', 'roles', 'permissions'])
            ->findOrFail($id);
    }

    public function findByEmail(string $email): User|null
    {
        return $this->model
            ->with(['worker', 'roles'])
            ->where('email', $email)
            ->first();
    }

    public function findByWorkerId(string $workerId)
    {
        return $this->model
            ->with(['roles'])
            ->where('worker_id', $workerId)
            ->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $user = $this->findById($id);
        return $user->update($data);
    }

    public function delete(string $id): bool
    {
        $user = $this->findById($id);
        return $user->delete();
    }

    public function updatePassword(string $id, string $password): bool
    {
        return $this->update($id, [
            'password' => bcrypt($password),
        ]);
    }

    public function syncRoles(string $id, array $roles): bool
    {
        $user = $this->findById($id);
        $user->syncRoles($roles);
        return true;
    }

    public function getActiveUsers(): Collection
    {
        return $this->model
            ->with(['worker.position'])
            ->where('is_active', true)
            ->get();
    }

    public function findByUsername(string $username): ?User
    {
        return $this->model
            ->with(['worker', 'roles'])
            ->where('username', $username)
            ->first();
    }

    public function isActive(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function updateLastLogin(User $user): bool
    {
        return $user->update([
            'last_login_at' => now(),
        ]);
    }

    public function getUserWithRelations(User $user, array $relations = []): User
    {
        return $user->load($relations);
    }

    public function getCachedUserData(string $userId): ?User
    {
        return cache()->remember("user.{$userId}", 3600, function () use ($userId) {
            return $this->findById($userId);
        });
    }

    public function cacheUserData(User $user, int $ttl = 3600): void
    {
        cache()->put("user.{$user->id}", $user, $ttl);
    }

    public function clearUserCache(string $userId): void
    {
        cache()->forget("user.{$userId}");
    }
}