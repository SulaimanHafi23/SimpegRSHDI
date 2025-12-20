<?php

namespace App\DTOs;

class UserDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $worker_id,
        public readonly ?string $email,
        public readonly ?string $username,
        public readonly ?string $password,
        public readonly ?string $email_verified_at,
        public readonly ?string $last_login,
        public readonly ?bool $is_active,
        public readonly ?array $roles = [],
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            worker_id: $data['worker_id'] ?? null,
            email: $data['email'] ?? null,
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
            email_verified_at: $data['email_verified_at'] ?? null,
            last_login: $data['last_login'] ?? null,
            is_active: $data['is_active'] ?? null,
            roles: $data['roles'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'worker_id' => $this->worker_id,
            'email' => $this->email,
            'username' => $this->username,
            'password' => $this->password,
            'email_verified_at' => $this->email_verified_at,
            'last_login' => $this->last_login,
            'is_active' => $this->is_active,
        ], fn($value) => $value !== null && $value !== '');
    }
}
