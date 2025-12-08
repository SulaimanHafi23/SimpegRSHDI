<?php
// filepath: app/Repositories/UserRepository.php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)
            ->with(['worker.position', 'worker.gender', 'worker.religion'])
            ->first();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)
            ->with(['worker.position', 'worker.gender', 'worker.religion'])
            ->first();
    }

    /**
     * Update user last login
     */
    public function updateLastLogin(User $user): bool
    {
        return $user->update([
            'last_login' => now(),
        ]);
    }

    /**
     * Check if user is active
     */
    public function isActive(User $user): bool
    {
        return $user->is_active && ($user->worker?->status === 'Active' || $user->worker === null);
    }

    /**
     * Get user with relationships
     */
    public function getUserWithRelations(User $user, array $relations = []): User
    {
        $defaultRelations = ['worker.position', 'roles', 'permissions'];
        $relations = array_merge($defaultRelations, $relations);

        return $user->load($relations);
    }

    /**
     * Cache user data
     */
    public function cacheUserData(User $user, int $ttl = 3600): void
    {
        Cache::put("user_data_{$user->id}", $user, $ttl);
    }

    /**
     * Get cached user data
     */
    public function getCachedUserData(string $userId): ?User
    {
        return Cache::get("user_data_{$userId}");
    }

    /**
     * Clear user cache
     */
    public function clearUserCache(string $userId): void
    {
        Cache::forget("user_data_{$userId}");
    }
}