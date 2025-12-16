<?php

namespace App\DTOs\Master;

class LeaveTypeDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly string $code,
        public readonly ?int $max_days_per_year,
        public readonly bool $requires_approval,
        public readonly bool $requires_attachment,
        public readonly int $days_notice,
        public readonly bool $is_active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            code: $data['code'],
            max_days_per_year: $data['max_days_per_year'] ?? null,
            requires_approval: $data['requires_approval'] ?? true,
            requires_attachment: $data['requires_attachment'] ?? false,
            days_notice: $data['days_notice'] ?? 0,
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'max_days_per_year' => $this->max_days_per_year,
            'requires_approval' => $this->requires_approval,
            'requires_attachment' => $this->requires_attachment,
            'days_notice' => $this->days_notice,
            'is_active' => $this->is_active,
        ], fn($value) => $value !== null);
    }
}
