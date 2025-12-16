<?php

namespace App\Repositories\Contracts\Master;

use App\DTOs\Master\LocationDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LocationRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function all(): Collection;
    public function active(): Collection;
    public function findById(string $id): ?object;
    public function getByName(string $name): ?object;
    public function create(LocationDTO $dto): object;
    public function update(string $id, LocationDTO $dto): object;
    public function delete(string $id): bool;
    public function toggleStatus(string $id): object;
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator;
}
