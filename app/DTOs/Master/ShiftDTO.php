<?php
// filepath: app/DTOs/Master/ShiftDTO.php

namespace App\DTOs\Master;

class ShiftDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly string $start_time,
        public readonly string $end_time,
        public readonly float $total_hours,
        public readonly int $grace_period_minutes,
        public readonly bool $is_overnight,
        public readonly bool $is_active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            start_time: $data['start_time'],
            end_time: $data['end_time'],
            total_hours: $data['total_hours'],
            grace_period_minutes: $data['grace_period_minutes'] ?? 15,
            is_overnight: $data['is_overnight'] ?? false,
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_hours' => $this->total_hours,
            'grace_period_minutes' => $this->grace_period_minutes,
            'is_overnight' => $this->is_overnight,
            'is_active' => $this->is_active,
        ], fn($value) => $value !== null);
    }
}