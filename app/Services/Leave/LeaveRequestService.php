<?php

namespace App\Services\Leave;

use App\DTOs\LeaveRequestDTO;
use App\Repositories\Contracts\Leave\LeaveRequestRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class LeaveRequestService
{
    // Leave type configurations (could be moved to config file)
    private const LEAVE_TYPES = [
        'annual' => [
            'name' => 'Cuti Tahunan',
            'has_quota' => true,
            'max_days' => 12,
            'requires_approval' => true,
        ],
        'sick' => [
            'name' => 'Cuti Sakit',
            'has_quota' => false,
            'max_days' => null,
            'requires_approval' => false,
            'requires_attachment' => true,
        ],
        'permission' => [
            'name' => 'Izin',
            'has_quota' => false,
            'max_days' => null,
            'requires_approval' => true,
        ],
        'maternity' => [
            'name' => 'Cuti Melahirkan',
            'has_quota' => true,
            'max_days' => 90,
            'requires_approval' => true,
        ],
        'marriage' => [
            'name' => 'Cuti Menikah',
            'has_quota' => true,
            'max_days' => 3,
            'requires_approval' => true,
        ],
        'bereavement' => [
            'name' => 'Cuti Duka',
            'has_quota' => true,
            'max_days' => 2,
            'requires_approval' => true,
        ],
        'unpaid' => [
            'name' => 'Cuti Tanpa Gaji',
            'has_quota' => false,
            'max_days' => null,
            'requires_approval' => true,
        ],
    ];

    public function __construct(
        private readonly LeaveRequestRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function getByWorker(string $workerId, ?string $year = null)
    {
        return $this->repository->getByWorker($workerId, $year);
    }

    public function getPending()
    {
        return $this->repository->getPending();
    }

    /**
     * Get leave type configuration
     */
    public function getLeaveTypeConfig(string $leaveType): array
    {
        return self::LEAVE_TYPES[$leaveType] ?? throw new \Exception('Tipe cuti tidak valid');
    }

    /**
     * Get all available leave types
     */
    public function getAvailableLeaveTypes(): array
    {
        return self::LEAVE_TYPES;
    }

    public function create(LeaveRequestDTO $dto, ?UploadedFile $attachment = null): array
    {
        try {
            DB::beginTransaction();

            // Get leave type configuration
            $leaveTypeConfig = $this->getLeaveTypeConfig($dto->leaveType);

            // Check for overlapping leave requests
            if ($this->repository->checkOverlap($dto->workerId, $dto->startDate, $dto->endDate)) {
                throw new \Exception('Anda sudah memiliki pengajuan cuti pada rentang tanggal tersebut');
            }
            // Check if attachment is required
            if (!empty($leaveTypeConfig['requires_attachment']) && !$attachment) {
                throw new \Exception('Bukti pendukung (surat keterangan) wajib dilampirkan untuk ' . $leaveTypeConfig['name']);
            }

            // Check leave quota if applicable
            if ($leaveTypeConfig['has_quota']) {
                $usedDays = $this->getUsedLeaveDays(
                    $dto->workerId, 
                    $dto->leaveType, 
                    date('Y', strtotime($dto->startDate))
                );
                $remainingDays = $leaveTypeConfig['max_days'] - $usedDays;

                if ($dto->totalDays > $remainingDays) {
                    throw new \Exception("Kuota {$leaveTypeConfig['name']} tidak mencukupi. Sisa kuota: {$remainingDays} hari");
                }
            }

            $data = $dto->toArray();

            // Upload attachment if provided
            if ($attachment) {
                $attachmentPath = $this->uploadAttachment($attachment);
                $data['attachment_url'] = $attachmentPath;
            }

            $leaveRequest = $this->repository->create($data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pengajuan cuti berhasil dibuat',
                'data' => $leaveRequest,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }

            Log::error('Error creating leave request: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(string $id, LeaveRequestDTO $dto, ?UploadedFile $attachment = null): array
    {
        try {
            DB::beginTransaction();

            $leaveRequest = $this->repository->findById($id);

            // Check if status is still pending
            if ($leaveRequest->status !== 'pending') {
                throw new \Exception('Hanya pengajuan dengan status pending yang dapat diubah');
            }

            // Get leave type configuration
            $leaveTypeConfig = $this->getLeaveTypeConfig($dto->leaveType);

            // Check for overlapping leave requests (exclude current)
            if ($this->repository->checkOverlap($dto->workerId, $dto->startDate, $dto->endDate, $id)) {
                throw new \Exception('Anda sudah memiliki pengajuan cuti pada rentang tanggal tersebut');
            }

            // Check leave quota if applicable
            if ($leaveTypeConfig['has_quota']) {
                $usedDays = $this->getUsedLeaveDays(
                    $dto->workerId, 
                    $dto->leaveType, 
                    date('Y', strtotime($dto->startDate)), 
                    $id
                );
                $remainingDays = $leaveTypeConfig['max_days'] - $usedDays;

                if ($dto->totalDays > $remainingDays) {
                    throw new \Exception("Kuota {$leaveTypeConfig['name']} tidak mencukupi. Sisa kuota: {$remainingDays} hari");
                }
            }

            $data = $dto->toArray();

            // Handle attachment replacement
            if ($attachment) {
                if ($leaveRequest->attachment_url) {
                    Storage::disk('public')->delete($leaveRequest->attachment_url);
                }
                $data['attachment_url'] = $this->uploadAttachment($attachment);
            }

            $updated = $this->repository->update($id, $data);

            if (!$updated) {
                throw new \Exception('Gagal mengupdate pengajuan cuti');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pengajuan cuti berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating leave request: ' . $e->getMessage());

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

            $leaveRequest = $this->repository->findById($id);

            // Check if status is still pending
            if ($leaveRequest->status !== 'pending') {
                throw new \Exception('Hanya pengajuan dengan status pending yang dapat dihapus');
            }

            // Delete attachment if exists
            if ($leaveRequest->attachment_url) {
                Storage::disk('public')->delete($leaveRequest->attachment_url);
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus pengajuan cuti');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pengajuan cuti berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting leave request: ' . $e->getMessage());

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

            $leaveRequest = $this->repository->findById($id);

            if ($leaveRequest->status !== 'pending') {
                throw new \Exception('Pengajuan sudah diproses sebelumnya');
            }

            $approved = $this->repository->approve($id, $userId);

            if (!$approved) {
                throw new \Exception('Gagal menyetujui pengajuan cuti');
            }

            DB::commit();

            // TODO: Send notification to worker

            return [
                'success' => true,
                'message' => 'Pengajuan cuti berhasil disetujui',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving leave request: ' . $e->getMessage());

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

            $leaveRequest = $this->repository->findById($id);

            if ($leaveRequest->status !== 'pending') {
                throw new \Exception('Pengajuan sudah diproses sebelumnya');
            }

            $rejected = $this->repository->reject($id, $userId, $reason);

            if (!$rejected) {
                throw new \Exception('Gagal menolak pengajuan cuti');
            }

            DB::commit();

            // TODO: Send notification to worker

            return [
                'success' => true,
                'message' => 'Pengajuan cuti ditolak',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting leave request: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get used leave days by worker and leave type
     */
    private function getUsedLeaveDays(string $workerId, string $leaveType, string $year, ?string $excludeId = null): int
    {
        $leaveRequests = $this->repository->getByWorker($workerId, $year);

        return $leaveRequests
            ->where('leave_type', $leaveType)
            ->where('status', 'approved')
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->sum('total_days');
    }

    /**
     * Get leave quota summary
     */
    public function getLeaveQuota(string $workerId, string $year): array
    {
        $quotas = [];

        foreach (self::LEAVE_TYPES as $type => $config) {
            if ($config['has_quota']) {
                $usedDays = $this->getUsedLeaveDays($workerId, $type, $year);
                $quotas[] = [
                    'leave_type' => $type,
                    'leave_type_name' => $config['name'],
                    'max_days' => $config['max_days'],
                    'used_days' => $usedDays,
                    'remaining_days' => $config['max_days'] - $usedDays,
                ];
            }
        }

        return $quotas;
    }

    /**
     * Get leave statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $leaveRequests = $this->repository->paginate(999999, $filters)->items();

        return [
            'total' => count($leaveRequests),
            'pending' => collect($leaveRequests)->where('status', 'pending')->count(),
            'approved' => collect($leaveRequests)->where('status', 'approved')->count(),
            'rejected' => collect($leaveRequests)->where('status', 'rejected')->count(),
            'cancelled' => collect($leaveRequests)->where('status', 'cancelled')->count(),
            'total_days' => collect($leaveRequests)
                ->where('status', 'approved')
                ->sum('total_days'),
        ];
    }

    /**
     * Upload attachment
     */
    private function uploadAttachment(UploadedFile $file): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('leave-attachments', $filename, 'public');
    }
}
