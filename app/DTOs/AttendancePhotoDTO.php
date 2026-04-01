<?php

namespace App\DTOs;

class AttendancePhotoDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $attendance_id,
        public readonly string $photo_path,
        public readonly string $photo_type,
        public readonly string $taken_at,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            attendance_id: $data['attendance_id'],
            photo_path: $data['photo_path'],
            photo_type: $data['photo_type'],
            taken_at: $data['taken_at'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'attendance_id' => $this->attendance_id,
            'photo_path' => $this->photo_path,
            'photo_type' => $this->photo_type,
            'taken_at' => $this->taken_at,
        ], fn($value) => $value !== null);
    }
}
