<?php
// filepath: app/DTOs/Master/ShiftDTO.php

namespace App\DTOs\Master;

class ShiftDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly ?string $description,
        public readonly bool $isActive,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            description: $data['description'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}