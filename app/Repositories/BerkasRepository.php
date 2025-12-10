<?php
// filepath: app/Repositories/BerkasRepository.php

namespace App\Repositories;

use App\Models\Berkas;
use App\Repositories\Contracts\BerkasRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BerkasRepository implements BerkasRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Berkas $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['worker.position', 'fileRequirement.documentType', 'verifiedBy']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        return $query
            ->orderBy('uploaded_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['worker.position', 'fileRequirement.documentType', 'verifiedBy'])
            ->findOrFail($id);
    }

    public function findByWorker(string $workerId): Collection
    {
        return $this->model
            ->with(['fileRequirement.documentType', 'verifiedBy'])
            ->where('worker_id', $workerId)
            ->orderBy('uploaded_at', 'desc')
            ->get();
    }

    public function findByWorkerAndRequirement(string $workerId, string $fileRequirementId)
    {
        return $this->model
            ->where('worker_id', $workerId)
            ->where('file_requirement_id', $fileRequirementId)
            ->first();
    }

    public function getPending(): Collection
    {
        return $this->model
            ->with(['worker.position', 'fileRequirement.documentType'])
            ->where('verification_status', 'pending')
            ->orderBy('uploaded_at', 'asc')
            ->get();
    }

    public function create(array $data)
    {
        $data['uploaded_at'] = now();
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $berkas = $this->findById($id);
        return $berkas->update($data);
    }

    public function delete(string $id): bool
    {
        $berkas = $this->findById($id);
        return $berkas->delete();
    }

    public function verify(string $id, string $userId, ?string $notes = null): bool
    {
        return $this->update($id, [
            'verification_status' => 'approved',
            'verified_by' => $userId,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    public function reject(string $id, string $userId, string $notes): bool
    {
        return $this->update($id, [
            'verification_status' => 'rejected',
            'verified_by' => $userId,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }
}
