<?php

namespace App\DTOs;

class LeaveRequestDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $worker_id,
        public readonly ?string $leave_type_id,
        public readonly ?string $start_date,
        public readonly ?string $end_date,
        public readonly ?int $total_days,
        public readonly ?string $reason,
        public readonly ?string $attachment_path,
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
            leave_type_id: $data['leave_type_id'] ?? null,
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            total_days: $data['total_days'] ?? null,
            reason: $data['reason'] ?? null,
            attachment_path: $data['attachment_path'] ?? null,
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
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'attachment_path' => $this->attachment_path,
            'status' => $this->status,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
        ], fn($value) => $value !== null && $value !== '');
    }
}
