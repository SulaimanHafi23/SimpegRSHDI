<?php

namespace App\DTOs;

class ShiftOverrideDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $worker_id,
        public readonly string $shift_id,
        public readonly string $override_date,
        public readonly ?string $reason,
        public readonly string $created_by,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            worker_id: $data['worker_id'],
            shift_id: $data['shift_id'],
            override_date: $data['override_date'],
            reason: $data['reason'] ?? null,
            created_by: $data['created_by'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'worker_id' => $this->worker_id,
            'shift_id' => $this->shift_id,
            'override_date' => $this->override_date,
            'reason' => $this->reason,
            'created_by' => $this->created_by,
        ], fn($value) => $value !== null);
    }
}
