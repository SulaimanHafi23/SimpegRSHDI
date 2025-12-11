<?php
// filepath: app/DTOs/Role/RoleDTO.php

namespace App\DTOs;

class RoleDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $guardName,
        public readonly array $permissions,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            guardName: $data['guard_name'] ?? 'web',
            permissions: $data['permissions'] ?? [],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'guard_name' => $this->guardName,
        ], fn($value) => !is_null($value));
    }
}
