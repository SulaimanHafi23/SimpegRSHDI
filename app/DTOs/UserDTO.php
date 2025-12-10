<?php

namespace App\DTOs\User;

class UserDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $workerId,
        public readonly string $email,
        public readonly ?string $password,
        public readonly ?string $emailVerifiedAt,
        public readonly bool $isActive,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            workerId: $data['worker_id'] ?? null,
            email: $data['email'],
            password: $data['password'] ?? null,
            emailVerifiedAt: $data['email_verified_at'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        $data = [
            'worker_id' => $this->workerId,
            'email' => $this->email,
            'is_active' => $this->isActive,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->emailVerifiedAt) {
            $data['email_verified_at'] = $this->emailVerifiedAt;
        }

        return array_filter($data, fn($value) => !is_null($value));
    }
}
