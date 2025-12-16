<?php

namespace App\Repositories\Contracts\Worker;

use App\DTOs\WorkerDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WorkerRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function getById(string $id): ?object;
    public function getByNip(string $nip): ?object;
    public function getByEmail(string $email): ?object;
    public function getByDepartment(string $departmentId): Collection;
    public function create(WorkerDTO $dto): object;
    public function update(string $id, WorkerDTO $dto): object;
    public function delete(string $id): bool;
    public function resign(string $id, string $resignDate): object;
    public function restore(string $id): object;
    public function updateStatus(string $id, string $status): object;
    public function updatePhoto(string $id, string $photoUrl): object;
}
