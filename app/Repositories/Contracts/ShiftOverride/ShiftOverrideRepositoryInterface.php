<?php

namespace App\Repositories\Contracts\ShiftOverride;

use App\DTOs\ShiftOverrideDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ShiftOverrideRepositoryInterface
{

    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getById(string $id): ?object;
    public function getByWorkerId(string $workerId): Collection;
    public function getByDate(string $date): Collection;
    public function getByWorkerAndDate(string $workerId, string $date): ?object;
    public function create(ShiftOverrideDTO $dto): object;
    public function update(string $id, ShiftOverrideDTO $dto): object;
    public function delete(string $id): bool;
}
