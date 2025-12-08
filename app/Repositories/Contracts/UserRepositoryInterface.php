<?php
// filepath: app/Repositories/Contracts/UserRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\User;

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
}