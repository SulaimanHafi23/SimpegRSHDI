<?php
// filepath: app/Repositories/WorkerDocument/WorkerDocumentRepository.php

namespace App\Repositories\WorkerDocument;

use App\DTOs\WorkerDocumentDTO;
use App\Models\WorkerDocument;
use App\Repositories\Contracts\WorkerDocument\WorkerDocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkerDocumentRepository implements WorkerDocumentRepositoryInterface
{
    public function __construct(
        protected WorkerDocument $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['worker', 'documentType', 'verifier']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['document_type_id'])) {
            $query->where('document_type_id', $filters['document_type_id']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Advanced search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('documentType', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->latest()
            ->paginate($filters['per_page'] ?? 15)
            ->appends($filters);
    }

    public function getById(string $id): ?object
    {
        return $this->model->with(['worker', 'documentType', 'verifier'])->find($id);
    }

    public function getByWorkerId(string $workerId): Collection
    {
        return $this->model->where('worker_id', $workerId)
            ->with(['documentType', 'verifier'])
            ->latest()
            ->get();
    }

    public function create(WorkerDocumentDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, WorkerDocumentDTO $dto): object
    {
        $document = $this->model->findOrFail($id);
        $document->update($dto->toArray());
        return $document->fresh();
    }

    public function delete(string $id): bool
    {
        $document = $this->model->findOrFail($id);
        
        // Delete file if exists
        if ($document->file_path && \Storage::exists($document->file_path)) {
            \Storage::delete($document->file_path);
        }
        
        return $document->delete();
    }

    public function verify(string $id, string $verifiedBy, ?string $notes = null): object
    {
        $document = $this->model->findOrFail($id);
        $document->update([
            'status' => 'verified',
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
            'notes' => $notes,
        ]);
        return $document->fresh();
    }

    public function reject(string $id, string $verifiedBy, string $notes): object
    {
        $document = $this->model->findOrFail($id);
        $document->update([
            'status' => 'rejected',
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
            'notes' => $notes,
        ]);
        return $document->fresh();
    }

    public function getExpiredDocuments(): Collection
    {
        return $this->model->whereNotNull('expired_date')
            ->where('expired_date', '<', now())
            ->where('status', 'verified')
            ->with(['worker', 'documentType'])
            ->get();
    }

    public function getExpiringDocuments(int $days = 30): Collection
    {
        return $this->model->whereNotNull('expired_date')
            ->where('expired_date', '<=', now()->addDays($days))
            ->where('expired_date', '>=', now())
            ->where('status', 'verified')
            ->with(['worker', 'documentType'])
            ->get();
    }
}
