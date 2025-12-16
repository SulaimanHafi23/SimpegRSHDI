<?php
// filepath: app/DTOs/Role/RoleDTO.php

namespace App\DTOs;

class RoleDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?array $permissions = [],
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            permissions: $data['permissions'] ?? [],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->permissions,
        ], fn($value) => $value !== null);
    }
}
