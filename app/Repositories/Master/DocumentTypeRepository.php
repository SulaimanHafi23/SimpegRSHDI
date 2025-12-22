<?php

namespace App\Repositories\Master;

use App\DTOs\Master\DocumentTypeDTO;
use App\Models\DocumentType;
use App\Repositories\Contracts\Master\DocumentTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentTypeRepository implements DocumentTypeRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected DocumentType $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query()->withCount('workerDocuments');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_required'])) {
            $query->where('is_required', $filters['is_required']);
        }

        if (isset($filters['has_expiry'])) {
            $query->where('has_expiry', $filters['has_expiry']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('code', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('workerDocuments')
            ->latest()
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    public function active(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function required(): Collection
    {
        return $this->model->where('is_required', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id): ?object
    {
        return $this->model->with(['workerDocuments'])
            ->withCount('workerDocuments')
            ->find($id);
    }

    public function getByName(string $name): ?object
    {
        return $this->model->where('name', $name)->first();
    }

    public function getByCode(string $code): ?object
    {
        // Some schemas may not include a 'code' column. Safely return null if column missing.
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn($this->model->getTable(), 'code')) {
                return null;
            }
            return $this->model->where('code', $code)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function create(DocumentTypeDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, DocumentTypeDTO $dto): object
    {
        $documentType = $this->model->findOrFail($id);
        $documentType->update($dto->toArray());
        return $documentType->fresh(['workerDocuments']);
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function toggleStatus(string $id): object
    {
        $documentType = $this->model->findOrFail($id);
        $documentType->update(['is_active' => !$documentType->is_active]);
        return $documentType->fresh();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('workerDocuments')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->latest()
            ->paginate($perPage);
    }
}
