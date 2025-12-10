<?php

namespace App\DTOs;

class BerkasDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $workerId,
        public readonly string $fileRequirementId,
        public readonly string $fileName,
        public readonly string $filePath,
        public readonly ?string $fileSize,
        public readonly ?string $notes,
        public readonly string $status,
        public readonly ?string $verifiedBy,
        public readonly ?string $verifiedAt,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            workerId: $data['worker_id'],
            fileRequirementId: $data['file_requirement_id'],
            fileName: $data['file_name'],
            filePath: $data['file_path'],
            fileSize: $data['file_size'] ?? null,
            notes: $data['notes'] ?? null,
            status: $data['status'] ?? 'pending',
            verifiedBy: $data['verified_by'] ?? null,
            verifiedAt: $data['verified_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'worker_id' => $this->workerId,
            'file_requirement_id' => $this->fileRequirementId,
            'file_name' => $this->fileName,
            'file_path' => $this->filePath,
            'file_size' => $this->fileSize,
            'notes' => $this->notes,
            'status' => $this->status,
            'verified_by' => $this->verifiedBy,
            'verified_at' => $this->verifiedAt,
        ], fn($value) => !is_null($value));
    }
}
