<?php

namespace App\DTOs;

use Carbon\Carbon;

class AbsentDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $workerId,
        public readonly string $locationId,
        public readonly string $date,
        public readonly string $checkIn,
        public readonly ?string $checkOut,
        public readonly ?string $checkInPhoto,
        public readonly ?string $checkOutPhoto,
        public readonly ?string $checkInLatitude,
        public readonly ?string $checkInLongitude,
        public readonly ?string $checkOutLatitude,
        public readonly ?string $checkOutLongitude,
        public readonly string $status,
        public readonly ?string $notes,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            workerId: $data['worker_id'],
            locationId: $data['location_id'],
            date: $data['date'],
            checkIn: $data['check_in'],
            checkOut: $data['check_out'] ?? null,
            checkInPhoto: $data['check_in_photo'] ?? null,
            checkOutPhoto: $data['check_out_photo'] ?? null,
            checkInLatitude: $data['check_in_latitude'] ?? null,
            checkInLongitude: $data['check_in_longitude'] ?? null,
            checkOutLatitude: $data['check_out_latitude'] ?? null,
            checkOutLongitude: $data['check_out_longitude'] ?? null,
            status: $data['status'] ?? 'present',
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'worker_id' => $this->workerId,
            'location_id' => $this->locationId,
            'date' => $this->date,
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'check_in_photo' => $this->checkInPhoto,
            'check_out_photo' => $this->checkOutPhoto,
            'check_in_latitude' => $this->checkInLatitude,
            'check_in_longitude' => $this->checkInLongitude,
            'check_out_latitude' => $this->checkOutLatitude,
            'check_out_longitude' => $this->checkOutLongitude,
            'status' => $this->status,
            'notes' => $this->notes,
        ], fn($value) => !is_null($value));
    }

    public function getWorkDuration(): ?string
    {
        if (!$this->checkOut) {
            return null;
        }

        $checkIn = Carbon::parse($this->date . ' ' . $this->checkIn);
        $checkOut = Carbon::parse($this->date . ' ' . $this->checkOut);

        // If checkout is before checkin, add a day (overnight)
        if ($checkOut->lessThan($checkIn)) {
            $checkOut->addDay();
        }

        $diff = $checkIn->diff($checkOut);
        return sprintf('%02d:%02d', $diff->h, $diff->i);
    }

    public function isLate(string $expectedCheckIn): bool
    {
        $expected = Carbon::parse($this->date . ' ' . $expectedCheckIn);
        $actual = Carbon::parse($this->date . ' ' . $this->checkIn);

        return $actual->greaterThan($expected);
    }

    public function isEarlyLeave(string $expectedCheckOut): bool
    {
        if (!$this->checkOut) {
            return false;
        }

        $expected = Carbon::parse($this->date . ' ' . $expectedCheckOut);
        $actual = Carbon::parse($this->date . ' ' . $this->checkOut);

        return $actual->lessThan($expected);
    }
}
