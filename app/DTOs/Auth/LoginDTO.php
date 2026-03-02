<?php
// filepath: app/DTOs/Auth/LoginDTO.php

namespace App\DTOs\Auth;

class LoginDTO
{
    public function __construct(
        public readonly string $login,
        public readonly string $password,
        public readonly bool $remember = false,
    ) {}

    /**
     * Create DTO from request data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            login: $data['login'],
            password: $data['password'],
            remember: $data['remember_me'] ?? false,
        );
    }

    /**
     * Get credentials for authentication
     * Auto-detect if login is email or username
     */
    public function getCredentials(): array
    {
        // Check if login is email
        if (filter_var($this->login, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => $this->login,
                'password' => $this->password,
            ];
        }

        // Otherwise treat as username
        return [
            'username' => $this->login,
            'password' => $this->password,
        ];
    }

    /**
     * Check if remember me is enabled
     */
    public function shouldRemember(): bool
    {
        return $this->remember;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'login' => $this->login,
            'password' => $this->password,
            'remember' => $this->remember,
        ];
    }
}