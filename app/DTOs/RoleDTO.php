<?php
// filepath: app/DTOs/Role/RoleDTO.php

namespace App\DTOs;

class RoleDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $display_name = null,
        public readonly ?string $description = null,
        public readonly ?array $permissions = [],
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            display_name: $data['display_name'] ?? null,
            description: $data['description'] ?? null,
            permissions: $data['permissions'] ?? [],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'permissions' => $this->permissions,
        ], fn($value) => $value !== null);
    }
}
