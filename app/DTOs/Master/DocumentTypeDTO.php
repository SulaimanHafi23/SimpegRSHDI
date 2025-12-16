<?php
// filepath: app/DTOs/Master/DocumentTypeDTO.php

namespace App\DTOs\Master;

class DocumentTypeDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $is_required,
        public readonly bool $is_active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
            is_required: $data['is_required'] ?? false,
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_required' => $this->is_required,
            'is_active' => $this->is_active,
        ], fn($value) => $value !== null);
    }
}