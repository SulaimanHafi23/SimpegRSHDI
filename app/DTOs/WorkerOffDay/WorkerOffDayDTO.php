<?php

namespace App\DTOs\WorkerOffDay;

class WorkerOffDayDTO
{
    public function __construct(
        public readonly string $worker_id,
        public readonly array $day_of_week, // [0, 3, 5]
        public readonly string $effective_from,
        public readonly ?string $effective_until,
        public readonly ?string $reason,
        public readonly ?string $created_by,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            worker_id: $data['worker_id'],
            day_of_week: array_map('intval', $data['day_of_week'] ?? []),
            effective_from: $data['effective_from'],
            effective_until: $data['effective_until'] ?? null,
            reason: $data['reason'] ?? null,
            created_by: $data['created_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'worker_id' => $this->worker_id,
            'day_of_week' => $this->day_of_week,
            'effective_from' => $this->effective_from,
            'effective_until' => $this->effective_until,
            'reason' => $this->reason,
            'created_by' => $this->created_by,
        ], fn($val) => $val !== null);
    }
}
