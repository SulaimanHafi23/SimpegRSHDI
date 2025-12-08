<?php
// filepath: app/DTOs/Auth/LoginDTO.php

namespace App\DTOs\Auth;

class LoginDTO
{
    public function __construct(
        public readonly string $login, // CHANGED: support email or username
        public readonly string $password,
        public readonly bool $rememberMe = false // default false
    ) {}

    /**
     * Create DTO from request data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            login: $data['login'], // from form field name="login"
            password: $data['password'],
            rememberMe: (bool) ($data['remember_me'] ?? false) // ensure boolean
        );
    }

    /**
     * Get credentials for authentication
     * Auto-detect if login is email or username
     */
    public function getCredentials(): array
    {
        $field = filter_var($this->login, FILTER_VALIDATE_EMAIL) 
            ? 'email' 
            : 'username';
        
        return [
            $field => $this->login,
            'password' => $this->password,
        ];
    }

    /**
     * Check if remember me is enabled
     */
    public function shouldRemember(): bool
    {
        return $this->rememberMe;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'login' => $this->login,
            'password' => $this->password,
            'remember_me' => $this->rememberMe,
        ];
    }
}