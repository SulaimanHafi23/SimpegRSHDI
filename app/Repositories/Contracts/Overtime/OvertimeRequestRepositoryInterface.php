<?php

namespace App\Repositories\Contracts\Overtime;

use App\DTOs\OvertimeRequestDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OvertimeRequestRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getById(string $id): ?object;
    public function getByWorkerId(string $workerId, array $filters = []): Collection;
    public function getPendingRequests(): Collection;
    public function create(OvertimeRequestDTO $dto): object;
    public function update(string $id, OvertimeRequestDTO $dto): object;
    public function delete(string $id): bool;
    public function approve(string $id, string $approvedBy): object;
    public function reject(string $id, string $approvedBy, string $reason): object;
}
