<?php
// filepath: app/Repositories/Contracts/WorkerDocument/WorkerDocumentRepositoryInterface.php

namespace App\Repositories\Contracts\WorkerDocument;

use App\DTOs\WorkerDocumentDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WorkerDocumentRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getById(string $id): ?object;
    public function getByWorkerId(string $workerId): Collection;
    public function create(WorkerDocumentDTO $dto): object;
    public function update(string $id, WorkerDocumentDTO $dto): object;
    public function delete(string $id): bool;
    public function verify(string $id, string $verifiedBy, ?string $notes = null): object;
    public function reject(string $id, string $verifiedBy, string $notes): object;
    public function getExpiredDocuments(): Collection;
    public function getExpiringDocuments(int $days = 30): Collection;
}
