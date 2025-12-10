<?php
// filepath: app/DTOs/Master/DocumentTypeDTO.php

namespace App\DTOs\Master;

class DocumentTypeDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly string $fileFormat,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            fileFormat: $data['file_format'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'file_format' => $this->fileFormat,
        ], fn($value) => !is_null($value));
    }
}