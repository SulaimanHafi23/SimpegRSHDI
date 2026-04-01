<?php

namespace App\DTOs;

class AttendanceDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $worker_id,
        public readonly ?string $shift_id,
        public readonly string $attendance_date,
        public readonly ?string $check_in,
        public readonly ?string $check_out,
        public readonly ?int $distance_check_in,
        public readonly ?int $distance_check_out,
        public readonly bool $check_in_by_admin,
        public readonly ?string $check_in_admin_id,
        public readonly bool $check_out_by_admin,
        public readonly ?string $check_out_admin_id,
        public readonly string $status,
        public readonly bool $is_late,
        public readonly int $late_minutes,
        public readonly bool $is_early_leave,
        public readonly int $early_leave_minutes,
        public readonly ?string $notes,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            worker_id: $data['worker_id'],
            shift_id: $data['shift_id'] ?? null,
            attendance_date: $data['attendance_date'],
            check_in: $data['check_in'] ?? null,
            check_out: $data['check_out'] ?? null,
            distance_check_in: $data['distance_check_in'] ?? null,
            distance_check_out: $data['distance_check_out'] ?? null,
            check_in_by_admin: $data['check_in_by_admin'] ?? false,
            check_in_admin_id: $data['check_in_admin_id'] ?? null,
            check_out_by_admin: $data['check_out_by_admin'] ?? false,
            check_out_admin_id: $data['check_out_admin_id'] ?? null,
            status: $data['status'] ?? 'present',
            is_late: $data['is_late'] ?? false,
            late_minutes: $data['late_minutes'] ?? 0,
            is_early_leave: $data['is_early_leave'] ?? false,
            early_leave_minutes: $data['early_leave_minutes'] ?? 0,
            notes: $data['notes'] ?? null,
        );
    }

    public static function fromArray(array $data): self
    {
        return self::fromRequest($data);
    }

    public function toArray(): array
    {
        return [
            'worker_id' => $this->worker_id,
            'shift_id' => $this->shift_id,
            'attendance_date' => $this->attendance_date,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'distance_check_in' => $this->distance_check_in,
            'distance_check_out' => $this->distance_check_out,
            'check_in_by_admin' => $this->check_in_by_admin,
            'check_in_admin_id' => $this->check_in_admin_id,
            'check_out_by_admin' => $this->check_out_by_admin,
            'check_out_admin_id' => $this->check_out_admin_id,
            'status' => $this->status,
            'is_late' => $this->is_late,
            'late_minutes' => $this->late_minutes,
            'is_early_leave' => $this->is_early_leave,
            'early_leave_minutes' => $this->early_leave_minutes,
            'notes' => $this->notes,
        ];
    }
}
