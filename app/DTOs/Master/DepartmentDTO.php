<?php
// filepath: app/DTOs/Master/PositionDTO.php

namespace App\DTOs\Master;

class DepartmentDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description,
        public readonly bool $is_active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            code: $data['code'],
            description: $data['description'] ?? null,
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ], fn($value) => $value !== null);
    }
}