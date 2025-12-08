<?php
// filepath: app/Services/Auth/AuthService.php

namespace App\Services\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Authenticate user
     * 
     * @throws ValidationException
     */
    public function login(LoginDTO $dto): array
    {
        // Find user (support username or email)
        $user = $this->findUser($dto->login);

        if (!$user) {
            Log::warning('Login attempt with invalid username/email', [
                'login' => $dto->login,
                'ip' => request()->ip(),
            ]);

            throw ValidationException::withMessages([
                'login' => ['Username atau email tidak ditemukan.'],
            ]);
        }

        // Check if user is active
        if (!$this->userRepository->isActive($user)) {
            Log::warning('Login attempt by inactive user', [
                'user_id' => $user->id,
                'login' => $user->username,
                'is_active' => $user->is_active,
                'worker_status' => $user->worker?->status,
            ]);

            throw ValidationException::withMessages([
                'username' => ['Akun Anda tidak aktif. Silakan hubungi administrator.'],
            ]);
        }

        // Verify password
        if (!Hash::check($dto->password, $user->password)) {
            Log::warning('Login attempt with invalid password', [
                'user_id' => $user->id,
                'login' => $user->username,
                'ip' => request()->ip(),
            ]);

            throw ValidationException::withMessages([
                'password' => ['Password yang Anda masukkan salah.'],
            ]);
        }

        // Login user
        Auth::login($user, $dto->rememberMe);

        // Update last login
        $this->userRepository->updateLastLogin($user);

        // Load relationships
        $user = $this->userRepository->getUserWithRelations($user);

        // Cache user data
        $this->userRepository->cacheUserData($user);

        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => request()->ip(),
        ]);

        return [
            'user' => $user,
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): void
    {
        // Clear cache
        $this->userRepository->clearUserCache($user->id);

        // Logout from session
        Auth::logout();

        Log::info('User logged out', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);
    }

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(): ?User
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        // Try to get from cache
        $cachedUser = $this->userRepository->getCachedUserData($user->id);

        if ($cachedUser) {
            return $cachedUser;
        }

        // Load relationships and cache
        $user = $this->userRepository->getUserWithRelations($user);
        $this->userRepository->cacheUserData($user);

        return $user;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }

    /**
     * Check if user has role
     */
    public function hasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Find user by username or email
     */
    private function findUser(string $identifier): ?User
    {
        // Try username first
        $user = $this->userRepository->findByUsername($identifier);

        // If not found, try email
        if (!$user && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->findByEmail($identifier);
        }

        return $user;
    }
}