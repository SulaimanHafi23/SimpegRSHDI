<?php

namespace App\DTOs\Worker;

use Carbon\Carbon;

class WorkerDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $nik,
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $genderId,
        public readonly string $religionId,
        public readonly string $positionId,
        public readonly string $placeOfBirth,
        public readonly string $dateOfBirth, // Y-m-d format
        public readonly string $address,
        public readonly string $hireDate, // Y-m-d format
        public readonly string $status,
        public readonly bool $isActive,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nik: $data['nik'],
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone_number'],
            genderId: $data['gender_id'],
            religionId: $data['religion_id'],
            positionId: $data['position_id'],
            placeOfBirth: $data['place_of_birth'],
            dateOfBirth: $data['date_of_birth'],
            address: $data['address'],
            hireDate: $data['hire_date'],
            status: $data['status'],
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'nik' => $this->nik,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender_id' => $this->genderId,
            'religion_id' => $this->religionId,
            'position_id' => $this->positionId,
            'place_of_birth' => $this->placeOfBirth,
            'date_of_birth' => $this->dateOfBirth,
            'address' => $this->address,
            'hire_date' => $this->hireDate,
            'status' => $this->status,
            'is_active' => $this->isActive,
        ];
    }

    public function getAge(): int
    {
        return Carbon::parse($this->dateOfBirth)->age;
    }

    public function getWorkDuration(): string
    {
        $hireDate = Carbon::parse($this->hireDate);
        $now = Carbon::now();
        
        $years = $hireDate->diffInYears($now);
        $months = $hireDate->copy()->addYears($years)->diffInMonths($now);
        
        if ($years > 0 && $months > 0) {
            return "{$years} tahun {$months} bulan";
        } elseif ($years > 0) {
            return "{$years} tahun";
        } else {
            return "{$months} bulan";
        }
    }
}
