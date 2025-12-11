<?php
// filepath: app/Repositories/Master/FileRequirementRepository.php

namespace App\Repositories\Master;

use App\Models\FileRequirement;
use App\Repositories\Contracts\Master\FileRequirementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FileRequirementRepository implements FileRequirementRepositoryInterface
{
    public function __construct(
        private readonly FileRequirement $model
    ) {}

    public function paginate(int $perPage = 15, ?string $positionId = null): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['position', 'documentType']);

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        return $query
            ->orderBy('is_mandatory', 'desc')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model
            ->with(['position', 'documentType'])
            ->orderBy('is_mandatory', 'desc')
            ->get();
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['position', 'documentType'])
            ->findOrFail($id);
    }

    public function findByPosition(string $positionId): Collection
    {
        return $this->model
            ->with('documentType')
            ->where('position_id', $positionId)
            ->orderBy('is_mandatory', 'desc')
            ->get();
    }

    public function findMandatoryByPosition(string $positionId): Collection
    {
        return $this->model
            ->with('documentType')
            ->where('position_id', $positionId)
            ->where('is_mandatory', true)
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $fileRequirement = $this->findById($id);
        return $fileRequirement->update($data);
    }

    public function delete(string $id): bool
    {
        $fileRequirement = $this->findById($id);
        return $fileRequirement->delete();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['position', 'documentType'])
            ->whereHas('position', function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->orWhereHas('documentType', function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->orderBy('is_mandatory', 'desc')
            ->paginate($perPage);
    }
}