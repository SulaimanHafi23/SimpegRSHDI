<?php
// filepath: app/DTOs/Master/DocumentTypeDTO.php

namespace App\DTOs\Master;

class DocumentTypeDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $file_format,
        public readonly ?int $max_file_size,
        public readonly bool $is_active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
            file_format: $data['file_format'] ?? ($data['allowed_extensions'] ?? null),
            max_file_size: isset($data['max_file_size']) ? (int) $data['max_file_size'] : null,
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'file_format' => $this->file_format,
            'max_file_size' => $this->max_file_size,
            'is_active' => $this->is_active,
        ], fn($value) => $value !== null);
    }
}