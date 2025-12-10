<?php

namespace App\Repositories\Master;

use App\Models\DocumentType;
use App\Repositories\Contracts\Master\DocumentTypeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DocumentTypeRepository implements DocumentTypeRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly DocumentType $model
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->withCount('fileRequirments')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id)
    {
        return $this->model->findOrFail($id);
    }

    public function withFileRequirements(string $id)
    {
        return $this->model
            ->with('fileRequirments.position')
            ->withCount('fileRequirments')
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $documentType = $this->findById($id);
        return $documentType->update($data);
    }

    public function delete(string $id): bool
    {
        $documentType = $this->findById($id);
        return $documentType->delete();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('file_format', 'like', "%{$keyword}%")
            ->withCount('fileRequirments')
            ->orderBy('name')
            ->paginate($perPage);
    }
}
