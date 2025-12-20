<?php

namespace App\DTOs;

class WorkerDocumentDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $worker_id,
        public readonly string $document_type_id,
        public readonly ?string $department_document_type_id,
        public readonly string $file_name,
        public readonly string $file_path,
        public readonly int $file_size,
        public readonly ?string $expired_date,
        public readonly string $status,
        public readonly ?string $verified_by,
        public readonly ?string $verified_at,
        public readonly ?string $notes,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            worker_id: $data['worker_id'],
            document_type_id: $data['document_type_id'],
            department_document_type_id: $data['department_document_type_id'] ?? null,
            file_name: $data['file_name'],
            file_path: $data['file_path'],
            file_size: $data['file_size'],
            expired_date: $data['expired_date'] ?? null,
            status: $data['status'] ?? 'pending',
            verified_by: $data['verified_by'] ?? null,
            verified_at: $data['verified_at'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'worker_id' => $this->worker_id,
            'document_type_id' => $this->document_type_id,
            'department_document_type_id' => $this->department_document_type_id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'expired_date' => $this->expired_date,
            'status' => $this->status,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at,
            'notes' => $this->notes,
        ], fn($value) => $value !== null);
    }
}
