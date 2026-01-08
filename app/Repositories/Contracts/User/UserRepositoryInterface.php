<?php
// filepath: app/Repositories/Contracts/User/UserRepositoryInterface.php

namespace App\Repositories\Contracts\User;

use App\DTOs\UserDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Get all users with pagination and filters
     */
    public function getAll(array $filters = []): LengthAwarePaginator;

    /**
     * Get user by ID
     */
    public function getById(string $id): ?object;

    /**
     * Get user by username
     */
    public function getByUsername(string $username): ?object;

    /**
     * Get user by email
     */
    public function getByEmail(string $email): ?object;

    /**
     * Get user by worker id
     */
    public function getByWorkerId(string $workerId): ?object;

    /**
     * Create a new user
     */
    public function create(UserDTO $dto): object;

    /**
     * Update an existing user
     */
    public function update(string $id, UserDTO $dto): object;

    /**
     * Delete a user
     */
    public function delete(string $id): bool;

    /**
     * Update user password
     */
    public function updatePassword(string $id, string $hashedPassword): object;

    /**
     * Update last login timestamp for a user
     */
    public function updateLastLogin(string $id): object;

    /**
     * Activate a user
     */
    public function activate(string $id): object;

    /**
     * Deactivate a user
     */
    public function deactivate(string $id): object;

    /**
     * Assign roles to a user
     */
    public function assignRoles(string $id, array $roles): object;

    /**
     * Sync user roles
     */
    public function syncRoles(string $id, array $roles): object;

    /**
     * Remove a role from a user
     */
    public function removeRole(string $id, string $role): object;
}