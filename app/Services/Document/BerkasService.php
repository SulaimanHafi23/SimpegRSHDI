<?php

namespace App\Services\Document;

use App\DTOs\BerkasDTO;
use App\Repositories\Contracts\Document\BerkasRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class BerkasService
{
    public function __construct(
        private readonly BerkasRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function getByWorker(string $workerId)
    {
        return $this->repository->findByWorker($workerId);
    }

    public function getPending()
    {
        return $this->repository->getPending();
    }

    public function upload(array $data, UploadedFile $file): array
    {
        try {
            DB::beginTransaction();

            // Check if document already exists
            $existing = $this->repository->findByWorkerAndRequirement(
                $data['worker_id'],
                $data['file_requirement_id']
            );

            if ($existing) {
                throw new \Exception('Dokumen untuk persyaratan ini sudah pernah diupload');
            }

            // Store file
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'public');

            $berkasData = [
                'worker_id' => $data['worker_id'],
                'file_requirement_id' => $data['file_requirement_id'],
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ];

            $berkas = $this->repository->create($berkasData);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'data' => $berkas,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            Log::error('Error uploading document: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(string $id, array $data, ?UploadedFile $file = null): array
    {
        try {
            DB::beginTransaction();

            $berkas = $this->repository->findById($id);
            $updateData = [];

            // Handle file replacement
            if ($file) {
                // Delete old file
                Storage::disk('public')->delete($berkas->file_path);

                // Store new file
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('documents', $fileName, 'public');

                $updateData['file_name'] = $fileName;
                $updateData['file_path'] = $filePath;
                $updateData['file_size'] = $file->getSize();
                $updateData['status'] = 'pending'; // Reset to pending when file replaced
            }

            if (isset($data['notes'])) {
                $updateData['notes'] = $data['notes'];
            }

            $updated = $this->repository->update($id, $updateData);

            if (!$updated) {
                throw new \Exception('Gagal mengupdate dokumen');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Dokumen berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating document: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $berkas = $this->repository->findById($id);

            // Delete file from storage
            Storage::disk('public')->delete($berkas->file_path);

            // Delete record
            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus dokumen');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Dokumen berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting document: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verify(string $id, string $userId): array
    {
        try {
            DB::beginTransaction();

            $verified = $this->repository->verify($id, $userId);

            if (!$verified) {
                throw new \Exception('Gagal memverifikasi dokumen');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Dokumen berhasil diverifikasi',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verifying document: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function reject(string $id, string $userId, string $reason): array
    {
        try {
            DB::beginTransaction();

            $rejected = $this->repository->reject($id, $userId, $reason);

            if (!$rejected) {
                throw new \Exception('Gagal menolak dokumen');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Dokumen ditolak',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting document: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
