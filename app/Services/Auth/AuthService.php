<?php

namespace App\Services\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Authenticate user
     */
    public function login(LoginDTO $dto): array
    {
        try {
            $credentials = $dto->getCredentials();

            // Check if user exists
            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email atau NIP tidak ditemukan.',
                ];
            }

            // Check if user is active
            if (!$user->is_active) {
                return [
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                ];
            }

            // Verify password
            if (!Hash::check($credentials['password'], $user->password)) {
                return [
                    'success' => false,
                    'message' => 'Password yang Anda masukkan salah.',
                ];
            }

            // Attempt authentication
            if (!Auth::attempt($credentials, $dto->shouldRemember())) {
                return [
                    'success' => false,
                    'message' => 'Gagal melakukan autentikasi.',
                ];
            }

            // Load relationships
            $user->load(['worker.department', 'roles.permissions']);

            // Update last login
            $user->update(['last_login' => now()]);

            return [
                'success' => true,
                'message' => 'Login berhasil!',
                'user' => $user,
            ];

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat login: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Logout user
     */
    public function logout(?User $user = null): void
    {
        if ($user && method_exists($user, 'tokens')) {
            // Revoke all tokens if using Sanctum
            $user->tokens()->delete();
        }

        Auth::logout();
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        return Auth::check();
    }

    /**
     * Get authenticated user
     */
    public function user(): ?User
    {
        return Auth::user();
    }

    /**
     * Get authenticated user with relationships
     */
    public function getAuthenticatedUser(): ?User
    {
        $user = Auth::user();

        if ($user) {
            return $user->load(['worker.department', 'roles.permissions']);
        }

        return null;
    }
}
