<?php

namespace App\DTOs;

use Carbon\Carbon;

class WorkerShiftScheduleDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $workerId,
        public readonly string $shiftId,
        public readonly string $shiftPatternId,
        public readonly string $scheduleDate,
        public readonly ?string $replacedWorkerId,
        public readonly ?string $notes,
        public readonly string $status,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            workerId: $data['worker_id'],
            shiftId: $data['shift_id'],
            shiftPatternId: $data['shift_pattern_id'],
            scheduleDate: $data['schedule_date'],
            replacedWorkerId: $data['replaced_worker_id'] ?? null,
            notes: $data['notes'] ?? null,
            status: $data['status'] ?? 'scheduled',
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'worker_id' => $this->workerId,
            'shift_id' => $this->shiftId,
            'shift_pattern_id' => $this->shiftPatternId,
            'schedule_date' => $this->scheduleDate,
            'replaced_worker_id' => $this->replacedWorkerId,
            'notes' => $this->notes,
            'status' => $this->status,
        ], fn($value) => !is_null($value));
    }

    public function isToday(): bool
    {
        return Carbon::parse($this->scheduleDate)->isToday();
    }

    public function isPast(): bool
    {
        return Carbon::parse($this->scheduleDate)->isPast();
    }

    public function isFuture(): bool
    {
        return Carbon::parse($this->scheduleDate)->isFuture();
    }
}
