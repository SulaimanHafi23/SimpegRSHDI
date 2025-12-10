<?php

namespace App\Services\Overtime;

use App\DTOs\Overtime\OvertimeDTO;
use App\Repositories\Contracts\Overtime\OvertimeRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class OvertimeService
{
    public function __construct(
        private readonly OvertimeRepositoryInterface $repository
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

    public function create(OvertimeDTO $dto, ?UploadedFile $attachment = null): array
    {
        try {
            DB::beginTransaction();

            $data = $dto->toArray();

            // Upload attachment if provided
            if ($attachment) {
                $attachmentPath = $this->uploadAttachment($attachment);
                $data['attachment_url'] = $attachmentPath;
            }

            $overtime = $this->repository->create($data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Lembur berhasil dibuat',
                'data' => $overtime,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }

            Log::error('Error creating overtime: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(string $id, OvertimeDTO $dto, ?UploadedFile $attachment = null): array
    {
        try {
            DB::beginTransaction();

            $overtime = $this->repository->findById($id);

            // Check if status is still pending
            if ($overtime->status !== 'pending') {
                throw new \Exception('Hanya lembur dengan status pending yang dapat diubah');
            }

            $data = $dto->toArray();

            // Handle attachment replacement
            if ($attachment) {
                if ($overtime->attachment_url) {
                    Storage::disk('public')->delete($overtime->attachment_url);
                }
                $data['attachment_url'] = $this->uploadAttachment($attachment);
            }

            $updated = $this->repository->update($id, $data);

            if (!$updated) {
                throw new \Exception('Gagal mengupdate lembur');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Lembur berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating overtime: ' . $e->getMessage());

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

            $overtime = $this->repository->findById($id);

            // Check if status is still pending
            if ($overtime->status !== 'pending') {
                throw new \Exception('Hanya lembur dengan status pending yang dapat dihapus');
            }

            // Delete attachment if exists
            if ($overtime->attachment_url) {
                Storage::disk('public')->delete($overtime->attachment_url);
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus lembur');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Lembur berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting overtime: ' . $e->getMessage());

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

            $overtime = $this->repository->findById($id);

            if ($overtime->status !== 'pending') {
                throw new \Exception('Lembur sudah diproses sebelumnya');
            }

            $approved = $this->repository->approve($id, $userId);

            if (!$approved) {
                throw new \Exception('Gagal menyetujui lembur');
            }

            DB::commit();

            // TODO: Send notification to worker

            return [
                'success' => true,
                'message' => 'Lembur berhasil disetujui',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving overtime: ' . $e->getMessage());

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

            $overtime = $this->repository->findById($id);

            if ($overtime->status !== 'pending') {
                throw new \Exception('Lembur sudah diproses sebelumnya');
            }

            $rejected = $this->repository->reject($id, $userId, $reason);

            if (!$rejected) {
                throw new \Exception('Gagal menolak lembur');
            }

            DB::commit();

            // TODO: Send notification to worker

            return [
                'success' => true,
                'message' => 'Lembur ditolak',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting overtime: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get overtime statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $overtimes = $this->repository->paginate(999999, $filters)->items();

        return [
            'total' => count($overtimes),
            'pending' => collect($overtimes)->where('status', 'pending')->count(),
            'approved' => collect($overtimes)->where('status', 'approved')->count(),
            'rejected' => collect($overtimes)->where('status', 'rejected')->count(),
            'total_hours' => collect($overtimes)
                ->where('status', 'approved')
                ->sum('duration_hours'),
            'total_minutes' => collect($overtimes)
                ->where('status', 'approved')
                ->sum('duration_minutes'),
        ];
    }

    /**
     * Calculate total overtime hours for worker
     */
    public function calculateTotalHours(string $workerId, string $startDate, string $endDate): float
    {
        $overtimes = $this->repository->getByWorker($workerId, $startDate, $endDate);
        
        return $overtimes
            ->where('status', 'approved')
            ->sum('duration_hours');
    }

    /**
     * Get overtime report for worker
     */
    public function getWorkerReport(string $workerId, string $year, ?string $month = null): array
    {
        $startDate = $month ? "$year-$month-01" : "$year-01-01";
        $endDate = $month 
            ? date("Y-m-t", strtotime($startDate)) 
            : "$year-12-31";
        
        $overtimes = $this->repository->getByWorker($workerId, $startDate, $endDate);

        return [
            'worker_id' => $workerId,
            'period' => $month ? date('F Y', strtotime($startDate)) : $year,
            'total_overtime' => $overtimes->count(),
            'approved' => $overtimes->where('status', 'approved')->count(),
            'pending' => $overtimes->where('status', 'pending')->count(),
            'rejected' => $overtimes->where('status', 'rejected')->count(),
            'total_hours' => $overtimes->where('status', 'approved')->sum('duration_hours'),
            'total_minutes' => $overtimes->where('status', 'approved')->sum('duration_minutes'),
            'details' => $overtimes->map(function ($overtime) {
                return [
                    'id' => $overtime->id,
                    'date' => $overtime->date,
                    'start_time' => $overtime->start_time,
                    'end_time' => $overtime->end_time,
                    'duration_hours' => $overtime->duration_hours,
                    'reason' => $overtime->reason,
                    'status' => $overtime->status,
                ];
            })->toArray(),
        ];
    }

    /**
     * Upload attachment
     */
    private function uploadAttachment(UploadedFile $file): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('overtimes', $filename, 'public');
    }
}
