<?php
// filepath: app/DTOs/Master/GenderDTO.php

namespace App\DTOs\Master;

class GenderDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
        ], fn($value) => !is_null($value));
    }
}