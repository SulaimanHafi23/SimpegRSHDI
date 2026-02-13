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
        public readonly bool $requires_holiday_attendance = false,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            code: $data['code'],
            description: $data['description'] ?? null,
            is_active: $data['is_active'] ?? true,
            requires_holiday_attendance: $data['requires_holiday_attendance'] ?? false,
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
            'requires_holiday_attendance' => $this->requires_holiday_attendance,
        ], fn($value) => $value !== null);
    }
}
