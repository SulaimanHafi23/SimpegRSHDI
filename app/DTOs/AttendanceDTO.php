<?php

namespace App\DTOs;

class AttendanceDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $worker_id,
        public readonly ?string $shift_id,
        public readonly ?string $location_id,
        public readonly string $attendance_date,
        public readonly ?string $check_in,
        public readonly ?string $check_out,
        public readonly ?float $check_in_latitude,
        public readonly ?float $check_in_longitude,
        public readonly ?float $check_out_latitude,
        public readonly ?float $check_out_longitude,
        public readonly ?int $distance_check_in,
        public readonly ?int $distance_check_out,
        public readonly string $status,
        public readonly bool $is_late,
        public readonly int $late_minutes,
        public readonly bool $is_early_leave,
        public readonly int $early_leave_minutes,
        public readonly bool $is_outside_radius,
        public readonly ?int $overtime_minutes,
        public readonly ?string $notes,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            worker_id: $data['worker_id'],
            shift_id: $data['shift_id'] ?? null,
            location_id: $data['location_id'] ?? null,
            attendance_date: $data['attendance_date'],
            check_in: $data['check_in'] ?? null,
            check_out: $data['check_out'] ?? null,
            check_in_latitude: $data['check_in_latitude'] ?? null,
            check_in_longitude: $data['check_in_longitude'] ?? null,
            check_out_latitude: $data['check_out_latitude'] ?? null,
            check_out_longitude: $data['check_out_longitude'] ?? null,
            distance_check_in: $data['distance_check_in'] ?? null,
            distance_check_out: $data['distance_check_out'] ?? null,
            status: $data['status'] ?? 'present',
            is_late: $data['is_late'] ?? false,
            late_minutes: $data['late_minutes'] ?? 0,
            is_early_leave: $data['is_early_leave'] ?? false,
            early_leave_minutes: $data['early_leave_minutes'] ?? 0,
            is_outside_radius: $data['is_outside_radius'] ?? false,
            overtime_minutes: $data['overtime_minutes'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public static function fromArray(array $data): self
    {
        return self::fromRequest($data);
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'worker_id' => $this->worker_id,
            'shift_id' => $this->shift_id,
            'location_id' => $this->location_id,
            'attendance_date' => $this->attendance_date,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'check_in_latitude' => $this->check_in_latitude,
            'check_in_longitude' => $this->check_in_longitude,
            'check_out_latitude' => $this->check_out_latitude,
            'check_out_longitude' => $this->check_out_longitude,
            'distance_check_in' => $this->distance_check_in,
            'distance_check_out' => $this->distance_check_out,
            'status' => $this->status,
            'is_late' => $this->is_late,
            'late_minutes' => $this->late_minutes,
            'is_early_leave' => $this->is_early_leave,
            'early_leave_minutes' => $this->early_leave_minutes,
            'is_outside_radius' => $this->is_outside_radius,
            'overtime_minutes' => $this->overtime_minutes,
            'notes' => $this->notes,
        ], fn($value) => $value !== null);
    }
}
