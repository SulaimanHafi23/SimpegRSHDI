<?php

namespace App\Services\Attendance;

use App\DTOs\Attendance\AbsentDTO;
use App\Repositories\Contracts\Attendance\AbsentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class AbsentService
{
    public function __construct(
        private readonly AbsentRepositoryInterface $repository
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

    public function getTodayAbsents()
    {
        return $this->repository->getTodayAbsents();
    }

    public function checkIn(array $data, ?UploadedFile $photo = null): array
    {
        try {
            DB::beginTransaction();

            // Check if already checked in today
            $existing = $this->repository->findByWorkerAndDate(
                $data['worker_id'],
                $data['date']
            );

            if ($existing) {
                throw new \Exception('Anda sudah melakukan check-in hari ini');
            }

            // Upload photo if provided
            if ($photo) {
                $photoPath = $this->uploadPhoto($photo, 'check-in');
                $data['check_in_photo'] = $photoPath;
            }

            // Determine status (late or present)
            // This should be checked against worker's shift schedule
            $data['status'] = $data['status'] ?? 'present';

            $absent = $this->repository->create($data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Check-in berhasil',
                'data' => $absent,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded photo if exists
            if (isset($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error('Error check-in: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function checkOut(string $absentId, array $data, ?UploadedFile $photo = null): array
    {
        try {
            DB::beginTransaction();

            $absent = $this->repository->findById($absentId);

            if ($absent->check_out) {
                throw new \Exception('Anda sudah melakukan check-out hari ini');
            }

            // Upload photo if provided
            if ($photo) {
                $photoPath = $this->uploadPhoto($photo, 'check-out');
                $data['check_out_photo'] = $photoPath;
            }

            $updated = $this->repository->checkOut($absentId, $data);

            if (!$updated) {
                throw new \Exception('Gagal melakukan check-out');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Check-out berhasil',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error('Error check-out: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(string $id, array $data, ?UploadedFile $checkInPhoto = null, ?UploadedFile $checkOutPhoto = null): array
    {
        try {
            DB::beginTransaction();

            $absent = $this->repository->findById($id);

            // Handle check-in photo replacement
            if ($checkInPhoto) {
                if ($absent->check_in_photo) {
                    Storage::disk('public')->delete($absent->check_in_photo);
                }
                $data['check_in_photo'] = $this->uploadPhoto($checkInPhoto, 'check-in');
            }

            // Handle check-out photo replacement
            if ($checkOutPhoto) {
                if ($absent->check_out_photo) {
                    Storage::disk('public')->delete($absent->check_out_photo);
                }
                $data['check_out_photo'] = $this->uploadPhoto($checkOutPhoto, 'check-out');
            }

            $updated = $this->repository->update($id, $data);

            if (!$updated) {
                throw new \Exception('Gagal mengupdate data absensi');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data absensi berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating absent: ' . $e->getMessage());

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

            $absent = $this->repository->findById($id);

            // Delete photos if exist
            if ($absent->check_in_photo) {
                Storage::disk('public')->delete($absent->check_in_photo);
            }
            if ($absent->check_out_photo) {
                Storage::disk('public')->delete($absent->check_out_photo);
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data absensi');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data absensi berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting absent: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get attendance statistics
     */
    public function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $absents = $this->repository->getByDateRange($startDate, $endDate);

        return [
            'total' => $absents->count(),
            'present' => $absents->where('status', 'present')->count(),
            'late' => $absents->where('status', 'late')->count(),
            'absent' => $absents->where('status', 'absent')->count(),
            'leave' => $absents->where('status', 'leave')->count(),
            'sick' => $absents->where('status', 'sick')->count(),
            'permission' => $absents->where('status', 'permission')->count(),
        ];
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $today = $this->repository->getTodayAbsents();
        $late = $this->repository->getLateToday();
        $absent = $this->repository->getAbsentToday();

        return [
            'today_total' => $today->count(),
            'today_late' => $late->count(),
            'today_absent' => $absent->count(),
            'today_present' => $today->where('status', 'present')->count(),
        ];
    }

    /**
     * Upload photo
     */
    private function uploadPhoto(UploadedFile $photo, string $type): string
    {
        $filename = time() . '_' . $type . '_' . $photo->getClientOriginalName();
        return $photo->storeAs('attendance', $filename, 'public');
    }

    /**
     * Calculate work hours
     */
    public function calculateWorkHours(string $workerId, string $startDate, string $endDate): array
    {
        $absents = $this->repository->getByWorker($workerId, $startDate, $endDate);
        
        $totalMinutes = 0;
        $workDays = 0;

        foreach ($absents as $absent) {
            if ($absent->check_out) {
                $checkIn = Carbon::parse($absent->date . ' ' . $absent->check_in);
                $checkOut = Carbon::parse($absent->date . ' ' . $absent->check_out);

                if ($checkOut->lessThan($checkIn)) {
                    $checkOut->addDay();
                }

                $totalMinutes += $checkIn->diffInMinutes($checkOut);
                $workDays++;
            }
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return [
            'total_days' => $workDays,
            'total_hours' => $hours,
            'total_minutes' => $minutes,
            'formatted' => sprintf('%d jam %d menit', $hours, $minutes),
            'average_per_day' => $workDays > 0 ? sprintf('%.2f jam', $totalMinutes / 60 / $workDays) : '0 jam',
        ];
    }
}
