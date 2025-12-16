<?php

namespace App\Repositories\Contracts\Master;

use App\DTOs\Master\LeaveTypeDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LeaveTypeRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function all(): Collection;
    public function active(): Collection;
    public function findById(string $id): ?object;
    public function getByName(string $name): ?object;
    public function getByCode(string $code): ?object;
    public function create(LeaveTypeDTO $dto): object;
    public function update(string $id, LeaveTypeDTO $dto): object;
    public function delete(string $id): bool;
    public function toggleStatus(string $id): object;
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator;
    public function getPaidLeaveTypes(): Collection;
    public function getRequiringApproval(): Collection;
}
