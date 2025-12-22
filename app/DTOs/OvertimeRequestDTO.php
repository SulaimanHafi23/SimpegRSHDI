<?php

namespace App\DTOs;

class OvertimeRequestDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $worker_id,
        public readonly ?string $overtime_date,
        public readonly ?string $start_time,
        public readonly ?string $end_time,
        public readonly ?int $total_hours,
        public readonly ?string $reason,
        public readonly ?string $status,
        public readonly ?string $approved_by,
        public readonly ?string $approved_at,
        public readonly ?string $rejection_reason,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            worker_id: $data['worker_id'] ?? null,
            overtime_date: $data['overtime_date'] ?? null,
            start_time: $data['start_time'] ?? null,
            end_time: $data['end_time'] ?? null,
            total_hours: $data['total_hours'] ?? null,
            reason: $data['reason'] ?? null,
            status: $data['status'] ?? null,
            approved_by: $data['approved_by'] ?? null,
            approved_at: $data['approved_at'] ?? null,
            rejection_reason: $data['rejection_reason'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'worker_id' => $this->worker_id,
            'overtime_date' => $this->overtime_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_hours' => $this->total_hours,
            'reason' => $this->reason,
            'status' => $this->status,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
        ], fn($value) => $value !== null && $value !== '');
    }
}
