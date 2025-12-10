<?php

namespace App\DTOs\Leave;

use Carbon\Carbon;

class LeaveRequestDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $workerId,
        public readonly string $leaveType,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $totalDays,
        public readonly string $reason,
        public readonly ?string $attachment,
        public readonly string $status,
        public readonly ?string $approvedBy,
        public readonly ?string $approvedAt,
        public readonly ?string $rejectionReason,
    ) {}

    public static function fromRequest(array $data): self
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        return new self(
            id: $data['id'] ?? null,
            workerId: $data['worker_id'],
            leaveType: $data['leave_type'],
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            totalDays: $totalDays,
            reason: $data['reason'],
            attachment: $data['attachment'] ?? null,
            status: $data['status'] ?? 'pending',
            approvedBy: $data['approved_by'] ?? null,
            approvedAt: $data['approved_at'] ?? null,
            rejectionReason: $data['rejection_reason'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'worker_id' => $this->workerId,
            'leave_type' => $this->leaveType,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'total_days' => $this->totalDays,
            'reason' => $this->reason,
            'attachment' => $this->attachment,
            'status' => $this->status,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt,
            'rejection_reason' => $this->rejectionReason,
        ], fn($value) => !is_null($value));
    }
}
