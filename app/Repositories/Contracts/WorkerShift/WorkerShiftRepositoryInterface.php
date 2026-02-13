<?php
// filepath: app/Repositories/Contracts/Schedule/WorkerShiftScheduleRepositoryInterface.php

namespace App\Repositories\Contracts\WorkerShift;

use App\DTOs\WorkerShiftDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WorkerShiftRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getById(string $id): ?object;
    public function getByWorkerId(string $workerId): Collection;
    public function getActiveByWorkerId(string $workerId): ?object;
    public function create(WorkerShiftDTO $dto): object;
    public function update(string $id, WorkerShiftDTO $dto): object;
    public function delete(string $id): bool;
    public function deactivateOldShifts(string $workerId, ?string $excludeId = null): void;
    public function deleteOldShifts(string $workerId, ?string $excludeId = null): int;
    public function updateOldShiftsEndDate(string $workerId, string $endDate): void;
}
