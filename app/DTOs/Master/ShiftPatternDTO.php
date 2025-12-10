<?php
// filepath: app/DTOs/Master/ShiftPatternDTO.php

namespace App\DTOs\Master;

class ShiftPatternDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $description,
        public readonly bool $isActive,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            type: $data['type'],
            description: $data['description'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}