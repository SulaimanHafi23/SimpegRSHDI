<?php

namespace App\Services\BusinessTrip;

use App\DTOs\BusinessTrip\BusinessTripDTO;
use App\Repositories\Contracts\BusinessTrip\BusinessTripRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BusinessTripService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly BusinessTripRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function getByWorker(string $workerId, ?string $startDate = null, ?string $endDate = null)
    {
        return $this->repository->getByWorker($workerId, $startDate, $endDate);
    }

    public function getPending()
    {
        return $this->repository->getPending();
    }

    public function getActive()
    {
        return $this->repository->getActive();
    }

    public function create(BusinessTripDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Check for overlapping business trips
            if ($this->repository->checkOverlap($dto->workerId, $dto->startDate, $dto->endDate)) {
                throw new \Exception('Pegawai sudah memiliki perjalanan dinas pada rentang tanggal tersebut');
            }

            $businessTrip = $this->repository->create($dto->toArray());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Perjalanan dinas berhasil dibuat',
                'data' => $businessTrip,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating business trip: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(string $id, BusinessTripDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $businessTrip = $this->repository->findById($id);

            // Check if status is still pending
            if ($businessTrip->status !== 'pending') {
                throw new \Exception('Hanya perjalanan dinas dengan status pending yang dapat diubah');
            }

            // Check for overlapping business trips (exclude current)
            if ($this->repository->checkOverlap($dto->workerId, $dto->startDate, $dto->endDate, $id)) {
                throw new \Exception('Pegawai sudah memiliki perjalanan dinas pada rentang tanggal tersebut');
            }

            $updated = $this->repository->update($id, $dto->toArray());

            if (!$updated) {
                throw new \Exception('Gagal mengupdate perjalanan dinas');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Perjalanan dinas berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating business trip: ' . $e->getMessage());

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

            $businessTrip = $this->repository->findById($id);

            // Check if status is still pending
            if ($businessTrip->status !== 'pending') {
                throw new \Exception('Hanya perjalanan dinas dengan status pending yang dapat dihapus');
            }

            // Check if has reports
            if ($businessTrip->reports()->exists()) {
                throw new \Exception('Tidak dapat menghapus perjalanan dinas yang sudah memiliki laporan');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus perjalanan dinas');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Perjalanan dinas berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting business trip: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function approve(string $id, string $userId): array
    {
        try {
            DB::beginTransaction();

            $businessTrip = $this->repository->findById($id);

            if ($businessTrip->status !== 'pending') {
                throw new \Exception('Perjalanan dinas sudah diproses sebelumnya');
            }

            $approved = $this->repository->approve($id, $userId);

            if (!$approved) {
                throw new \Exception('Gagal menyetujui perjalanan dinas');
            }

            DB::commit();

            // TODO: Send notification to worker

            return [
                'success' => true,
                'message' => 'Perjalanan dinas berhasil disetujui',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving business trip: ' . $e->getMessage());

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

            $businessTrip = $this->repository->findById($id);

            if ($businessTrip->status !== 'pending') {
                throw new \Exception('Perjalanan dinas sudah diproses sebelumnya');
            }

            $rejected = $this->repository->reject($id, $userId, $reason);

            if (!$rejected) {
                throw new \Exception('Gagal menolak perjalanan dinas');
            }

            DB::commit();

            // TODO: Send notification to worker

            return [
                'success' => true,
                'message' => 'Perjalanan dinas ditolak',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting business trip: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get business trip statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $businessTrips = $this->repository->paginate(999999, $filters)->items();

        return [
            'total' => count($businessTrips),
            'pending' => collect($businessTrips)->where('status', 'pending')->count(),
            'approved' => collect($businessTrips)->where('status', 'approved')->count(),
            'rejected' => collect($businessTrips)->where('status', 'rejected')->count(),
            'completed' => collect($businessTrips)->where('status', 'completed')->count(),
            'total_cost' => collect($businessTrips)
                ->where('status', 'approved')
                ->sum('total_cost'),
        ];
    }

    /**
     * Calculate total business trip cost for worker
     */
    public function calculateTotalCost(string $workerId, string $startDate, string $endDate): float
    {
        $businessTrips = $this->repository->getByWorker($workerId, $startDate, $endDate);
        
        return $businessTrips
            ->where('status', 'approved')
            ->sum('total_cost');
    }

    /**
     * Get business trip summary for worker
     */
    public function getWorkerSummary(string $workerId, string $year): array
    {
        $startDate = "$year-01-01";
        $endDate = "$year-12-31";
        
        $businessTrips = $this->repository->getByWorker($workerId, $startDate, $endDate);

        return [
            'total_trips' => $businessTrips->count(),
            'approved_trips' => $businessTrips->where('status', 'approved')->count(),
            'pending_trips' => $businessTrips->where('status', 'pending')->count(),
            'rejected_trips' => $businessTrips->where('status', 'rejected')->count(),
            'total_days' => $businessTrips->where('status', 'approved')->sum('total_days'),
            'total_cost' => $businessTrips->where('status', 'approved')->sum('total_cost'),
        ];
    }
}
