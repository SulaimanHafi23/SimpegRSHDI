<?php

namespace App\DTOs;

class WorkerShiftDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $worker_id,
        public readonly ?string $shift_id,
        public readonly ?string $pattern_type,
        public readonly ?array $rotating_days,
        public readonly ?array $custom_working_days,
        public readonly ?string $effective_from,
        public readonly ?string $effective_until,
        public readonly ?bool $is_active,
        public readonly ?string $notes,
    ) {}

    public static function fromRequest(array $data): self
    {
        // Support both start_date and effective_from naming
        $effectiveFrom = $data['effective_from'] ?? $data['start_date'] ?? null;
        $effectiveUntil = $data['effective_until'] ?? $data['end_date'] ?? null;
        
        return new self(
            id: $data['id'] ?? null,
            worker_id: $data['worker_id'] ?? null,
            shift_id: $data['shift_id'] ?? null,
            pattern_type: $data['pattern_type'] ?? null,
            rotating_days: $data['rotating_days'] ?? null,
            custom_working_days: $data['custom_working_days'] ?? $data['custom_days'] ?? null,
            effective_from: $effectiveFrom,
            effective_until: $effectiveUntil,
            is_active: $data['is_active'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'worker_id' => $this->worker_id,
            'shift_id' => $this->shift_id,
            'pattern_type' => $this->pattern_type,
            'rotating_days' => $this->rotating_days,
            'custom_working_days' => $this->custom_working_days,
            'effective_from' => $this->effective_from,
            'effective_until' => $this->effective_until,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ], fn($value) => $value !== null && $value !== '' && $value !== []);
    }
}
