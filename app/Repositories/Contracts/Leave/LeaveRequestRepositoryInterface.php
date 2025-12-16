<?php
// filepath: app/Repositories/Contracts/Leave/LeaveRequestRepositoryInterface.php

namespace App\Repositories\Contracts\Leave;

use App\DTOs\LeaveRequestDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LeaveRequestRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getById(string $id): ?object;
    public function getByWorkerId(string $workerId, array $filters = []): Collection;
    public function getPendingRequests(): Collection;
    public function create(LeaveRequestDTO $dto): object;
    public function update(string $id, LeaveRequestDTO $dto): object;
    public function delete(string $id): bool;
    public function approve(string $id, string $approvedBy): object;
    public function reject(string $id, string $approvedBy, string $reason): object;
    public function cancel(string $id): object;
    public function getWorkerLeaveBalance(string $workerId, string $leaveTypeId, int $year): int;
}
