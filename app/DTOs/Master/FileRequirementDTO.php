<?php

namespace App\DTOs\Master;

class FileRequirementDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $positionId,
        public readonly string $documentTypeId,
        public readonly bool $isMandatory,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            positionId: $data['position_id'],
            documentTypeId: $data['document_type_id'],
            isMandatory: (bool) ($data['is_mandatory'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'position_id' => $this->positionId,
            'document_type_id' => $this->documentTypeId,
            'is_mandatory' => $this->isMandatory,
        ];
    }
}
