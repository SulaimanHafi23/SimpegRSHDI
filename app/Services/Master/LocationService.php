<?php

namespace App\Services\Master;

use App\DTOs\Master\LocationDTO;
use App\Repositories\Contracts\Master\LocationRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationService
{
    public function __construct(
        private readonly LocationRepositoryInterface $repository
    ) {}

    /**
     * Get all locations with pagination
     */
    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Get all locations with filters
     */
    public function getAll(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Get all active locations
     */
    public function getAllActive()
    {
        return Cache::remember('master_locations_active', 3600, function () {
            return $this->repository->active();
        });
    }

    /**
     * Find location by ID
     */
    public function findById(string $id)
    {
        $location = $this->repository->findById($id);

        if (!$location) {
            throw new \Exception('Lokasi tidak ditemukan');
        }

        return $location;
    }

    /**
     * Get location by name
     */
    public function getByName(string $name)
    {
        return $this->repository->getByName($name);
    }

    /**
     * Create new location
     */
    public function create(LocationDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Check if location name already exists
            if ($this->repository->getByName($dto->name)) {
                throw new \Exception('Nama lokasi sudah digunakan');
            }

            $location = $this->repository->create($dto);

            DB::commit();
            Cache::forget('master_locations_active');

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
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update existing location
     */
    public function update(string $id, LocationDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $location = $this->findById($id);

            // Check if name already exists (except current)
            $existingByName = $this->repository->getByName($dto->name);
            if ($existingByName && $existingByName->id !== $id) {
                throw new \Exception('Nama lokasi sudah digunakan');
            }

            $location = $this->repository->update($id, $dto);

            DB::commit();
            Cache::forget('master_locations_active');

            return [
                'success' => true,
                'message' => 'Lokasi berhasil diperbarui',
                'data' => $location,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating location: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete location
     */
    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $location = $this->findById($id);

            // Check if location has attendances
            if ($location->attendances()->exists()) {
                throw new \Exception('Lokasi tidak dapat dihapus karena memiliki data absensi');
            }

            $this->repository->delete($id);

            DB::commit();
            Cache::forget('master_locations_active');

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

    /**
     * Toggle location status
     */
    public function toggleStatus(string $id): array
    {
        try {
            DB::beginTransaction();

            $location = $this->repository->toggleStatus($id);

            DB::commit();
            Cache::forget('master_locations_active');

            return [
                'success' => true,
                'message' => 'Status lokasi berhasil diubah',
                'data' => $location,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling location status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search locations
     */
    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }

    /**
     * Check if coordinates are within location radius
     */
    public function isWithinRadius(string $locationId, float $latitude, float $longitude): bool
    {
        $location = $this->findById($locationId);

        $distance = $this->calculateDistance(
            $location->latitude,
            $location->longitude,
            $latitude,
            $longitude
        );

        return $distance <= $location->radius;
    }

    /**
     * Calculate distance between two coordinates (in meters)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get nearest location
     */
    public function getNearestLocation(float $latitude, float $longitude)
    {
        $locations = $this->getAllActive();
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            $distance = $this->calculateDistance(
                $location->latitude,
                $location->longitude,
                $latitude,
                $longitude
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $location;
            }
        }

        return $nearest;
    }
}
