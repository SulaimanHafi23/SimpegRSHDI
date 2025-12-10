<?php

namespace App\Services\Master;

use App\DTOs\Master\ShiftPatternDTO;
use App\Repositories\Contracts\Master\ShiftPatternRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftPatternService
{
    public function __construct(
        private readonly ShiftPatternRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getActive()
    {
        return $this->repository->active();
    }

    public function getByType(string $type)
    {
        return $this->repository->findByType($type);
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function create(ShiftPatternDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $shiftPattern = $this->repository->create($dto->toArray());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pola shift berhasil ditambahkan',
                'data' => $shiftPattern,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating shift pattern: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambahkan pola shift: ' . $e->getMessage(),
            ];
        }
    }

    public function update(string $id, ShiftPatternDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $updated = $this->repository->update($id, $dto->toArray());

            if (!$updated) {
                throw new \Exception('Gagal mengupdate data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pola shift berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating shift pattern: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate pola shift: ' . $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $shiftPattern = $this->repository->findById($id);
            
            if ($shiftPattern->workerShiftSchedules()->exists()) {
                throw new \Exception('Pola shift tidak dapat dihapus karena masih digunakan dalam jadwal pegawai');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pola shift berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting shift pattern: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }

    /**
     * Get pattern type display name
     */
    public function getTypeDisplayName(string $type): string
    {
        return match($type) {
            'daily' => 'Setiap Hari',
            'weekdays' => 'Hari Kerja (Senin-Jumat)',
            'weekends' => 'Akhir Pekan (Sabtu-Minggu)',
            'custom' => 'Custom',
            default => $type,
        };
    }

    /**
     * Get available pattern types
     */
    public function getAvailableTypes(): array
    {
        return [
            'daily' => 'Setiap Hari',
            'weekdays' => 'Hari Kerja (Senin-Jumat)',
            'weekends' => 'Akhir Pekan (Sabtu-Minggu)',
            'custom' => 'Custom',
        ];
    }
}
