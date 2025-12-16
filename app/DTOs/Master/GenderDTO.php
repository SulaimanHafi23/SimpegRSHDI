<?php
// filepath: app/DTOs/Master/GenderDTO.php

namespace App\DTOs\Master;

class GenderDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly bool $is_active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
        ], fn($value) => $value !== null);
    }
}