<?php

namespace App\Services\Master;

use App\DTOs\Master\LocationDTO;
use App\Repositories\Contracts\Master\LocationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationService
{
    public function __construct(
        private readonly LocationRepositoryInterface $repository
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

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function create(LocationDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $location = $this->repository->create($dto->toArray());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Lokasi berhasil ditambahkan',
                'data' => $location,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating location: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambahkan lokasi: ' . $e->getMessage(),
            ];
        }
    }

    public function update(string $id, LocationDTO $dto): array
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
                'message' => 'Lokasi berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating location: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate lokasi: ' . $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $location = $this->repository->findById($id);
            
            if ($location->absents()->exists()) {
                throw new \Exception('Lokasi tidak dapat dihapus karena memiliki data absensi');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Lokasi berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting location: ' . $e->getMessage());

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
     * Calculate distance between two coordinates (Haversine formula)
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within location radius
     */
    public function isWithinRadius(string $locationId, float $lat, float $lon): bool
    {
        $location = $this->findById($locationId);

        if (!$location->latitude || !$location->longitude) {
            return true; // No geofence if coordinates not set
        }

        if (!$location->enforce_geofence) {
            return true; // Geofence not enforced
        }

        $distance = $this->calculateDistance(
            $location->latitude,
            $location->longitude,
            $lat,
            $lon
        );

        return $distance <= $location->radius;
    }
}
