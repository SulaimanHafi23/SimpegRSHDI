<?php
// filepath: app/DTOs/Auth/LoginDTO.php

namespace App\DTOs\Auth;

class LoginDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
        public readonly bool $remember = false
    ) {}

    /**
     * Create DTO from request data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            username: $data['username'],
            password: $data['password'],
            remember: $data['remember'] ?? false
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
            'remember' => $this->remember,
        ];
    }
}