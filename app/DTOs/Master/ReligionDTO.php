<?php
// filepath: app/DTOs/Master/ReligionDTO.php

namespace App\DTOs\Master;

class ReligionDTO
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?bool $isActive = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ], fn($value) => $value !== null);
    }
}