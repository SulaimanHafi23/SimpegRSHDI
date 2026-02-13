<?php

namespace App\DTOs;

class WorkerShiftDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $worker_id,
        public readonly ?string $shift_id,
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
            'effective_from' => $this->effective_from,
            'effective_until' => $this->effective_until,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ], fn($value) => $value !== null && $value !== '' && $value !== []);

        // Note: is_active is handled separately since false is a valid value
    }

    /**
     * Convert to array for Eloquent, preserving boolean false values
     */
    public function toUpdateArray(): array
    {
        $data = [];

        if ($this->id !== null) $data['id'] = $this->id;
        if ($this->worker_id !== null) $data['worker_id'] = $this->worker_id;
        if ($this->shift_id !== null && $this->shift_id !== '') $data['shift_id'] = $this->shift_id;
        if ($this->effective_from !== null && $this->effective_from !== '') $data['effective_from'] = $this->effective_from;
        if ($this->effective_until !== null) $data['effective_until'] = $this->effective_until;
        if ($this->is_active !== null) $data['is_active'] = $this->is_active; // preserves false
        if ($this->notes !== null) $data['notes'] = $this->notes;

        return $data;
    }
}
