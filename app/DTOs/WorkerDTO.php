<?php

namespace App\DTOs;

class WorkerDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $nip,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $phone_number,
        public readonly ?string $address,
        public readonly ?string $birth_date,
        public readonly ?string $birth_place,
        public readonly ?string $gender_id,
        public readonly ?string $religion_id,
        public readonly ?string $department_id,
        public readonly ?string $hire_date,
        public readonly ?string $resign_date,
        public readonly ?string $employment_status,
        public readonly ?string $status,
        public readonly ?string $photo_url,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nip: $data['nip'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phone_number: $data['phone_number'] ?? null,
            address: $data['address'] ?? null,
            birth_date: $data['birth_date'] ?? null,
            birth_place: $data['birth_place'] ?? null,
            gender_id: $data['gender_id'] ?? null,
            religion_id: $data['religion_id'] ?? null,
            department_id: $data['department_id'] ?? null,
            hire_date: $data['hire_date'] ?? null,
            resign_date: $data['resign_date'] ?? null,
            employment_status: $data['employment_status'] ?? null,
            status: $data['status'] ?? null,
            photo_url: $data['photo_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'nip' => $this->nip,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'gender_id' => $this->gender_id,
            'religion_id' => $this->religion_id,
            'department_id' => $this->department_id,
            'hire_date' => $this->hire_date,
            'resign_date' => $this->resign_date,
            'employment_status' => $this->employment_status,
            'status' => $this->status,
            'photo_url' => $this->photo_url,
        ], fn($value) => $value !== null && $value !== '');
    }
}
