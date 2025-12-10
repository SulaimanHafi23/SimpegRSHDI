<?php

namespace App\DTOs\Overtime;

use Carbon\Carbon;

class OvertimeDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $workerId,
        public readonly string $date,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly int $durationMinutes,
        public readonly float $durationHours,
        public readonly string $reason,
        public readonly ?string $notes,
        public readonly string $status,
        public readonly ?string $approvedBy,
        public readonly ?string $approvedAt,
        public readonly ?string $rejectionReason,
        public readonly ?string $attachmentUrl,
    ) {}

    public static function fromRequest(array $data): self
    {
        $startTime = Carbon::parse($data['date'] . ' ' . $data['start_time']);
        $endTime = Carbon::parse($data['date'] . ' ' . $data['end_time']);
        
        // If end time is before start time, assume it's next day
        if ($endTime->lessThan($startTime)) {
            $endTime->addDay();
        }
        
        $durationMinutes = $startTime->diffInMinutes($endTime);
        $durationHours = round($durationMinutes / 60, 2);

        return new self(
            id: $data['id'] ?? null,
            workerId: $data['worker_id'],
            date: $data['date'],
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            durationMinutes: $durationMinutes,
            durationHours: $durationHours,
            reason: $data['reason'],
            notes: $data['notes'] ?? null,
            status: $data['status'] ?? 'pending',
            approvedBy: $data['approved_by'] ?? null,
            approvedAt: $data['approved_at'] ?? null,
            rejectionReason: $data['rejection_reason'] ?? null,
            attachmentUrl: $data['attachment_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'worker_id' => $this->workerId,
            'date' => $this->date,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'duration_minutes' => $this->durationMinutes,
            'duration_hours' => $this->durationHours,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'status' => $this->status,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt,
            'rejection_reason' => $this->rejectionReason,
            'attachment_url' => $this->attachmentUrl,
        ], fn($value) => !is_null($value));
    }
}
