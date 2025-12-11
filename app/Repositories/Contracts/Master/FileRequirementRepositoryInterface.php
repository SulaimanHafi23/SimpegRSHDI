<?php

namespace App\Repositories\Contracts\Master;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FileRequirementRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $positionId = null): LengthAwarePaginator;
    public function all(): Collection;
    public function findById(string $id);
    public function findByPosition(string $positionId): Collection;
    public function findMandatoryByPosition(string $positionId): Collection;
    public function create(array $data);
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator;
}
