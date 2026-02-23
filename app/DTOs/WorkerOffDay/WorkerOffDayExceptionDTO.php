<?php

namespace App\DTOs\WorkerOffDay;

class WorkerOffDayExceptionDTO
{
    public function __construct(
        public readonly string $worker_id,
        public readonly string $off_date,
        public readonly string $type, // 'single' | 'recurring'
        public readonly ?array $recurring_pattern,
        public readonly ?string $reason,
        public readonly ?string $created_by,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            worker_id: $data['worker_id'],
            off_date: $data['off_date'],
            type: $data['type'] ?? 'single',
            recurring_pattern: $data['recurring_pattern'] ?? null,
            reason: $data['reason'] ?? null,
            created_by: $data['created_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'worker_id' => $this->worker_id,
            'off_date' => $this->off_date,
            'type' => $this->type,
            'recurring_pattern' => $this->recurring_pattern,
            'reason' => $this->reason,
            'created_by' => $this->created_by,
        ], fn($val) => $val !== null);
    }
}
