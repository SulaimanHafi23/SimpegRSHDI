<?php
// filepath: app/Repositories/Contracts/UserRepositoryInterface.php

namespace App\Repositories\Contracts\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Update user last login
     */
    public function updateLastLogin(User $user): bool;

    /**
     * Check if user is active
     */
    public function isActive(User $user): bool;

    /**
     * Get user with relationships
     */
    public function getUserWithRelations(User $user, array $relations = []): User;

    /**
     * Cache user data
     */
    public function cacheUserData(User $user, int $ttl = 3600): void;

    /**
     * Get cached user data
     */
    public function getCachedUserData(string $userId): ?User;

    /**
     * Clear user cache
     */
    public function clearUserCache(string $userId): void;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function getAll(): Collection;
    public function findById(string $id);
    public function findByWorkerId(string $workerId);
    public function create(array $data);
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
    public function updatePassword(string $id, string $password): bool;
    public function syncRoles(string $id, array $roles): bool;
    public function getActiveUsers(): Collection;
}