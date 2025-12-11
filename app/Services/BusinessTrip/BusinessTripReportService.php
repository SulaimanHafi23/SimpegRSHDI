<?php

namespace App\Services\BusinessTrip;

use App\DTOs\BusinessTripReportDTO;
use App\Repositories\Contracts\BusinessTrip\BusinessTripReportRepositoryInterface;
use App\Repositories\Contracts\BusinessTrip\BusinessTripRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class BusinessTripReportService
{
    public function __construct(
        private readonly BusinessTripReportRepositoryInterface $repository,
        private readonly BusinessTripRepositoryInterface $businessTripRepository
    ) {}

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function getByBusinessTrip(string $businessTripId)
    {
        return $this->repository->getByBusinessTrip($businessTripId);
    }

    public function getPendingReview()
    {
        return $this->repository->getPendingReview();
    }

    public function create(BusinessTripReportDTO $dto, ?UploadedFile $attachment = null): array
    {
        try {
            DB::beginTransaction();

            // Check if business trip exists and is approved
            $businessTrip = $this->businessTripRepository->findById($dto->businessTripId);

            if ($businessTrip->status !== 'approved') {
                throw new \Exception('Hanya perjalanan dinas yang sudah disetujui yang dapat dilaporkan');
            }

            $data = $dto->toArray();

            // Upload attachment if provided
            if ($attachment) {
                $attachmentPath = $this->uploadAttachment($attachment);
                $data['attachment_url'] = $attachmentPath;
            }

            $report = $this->repository->create($data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Laporan perjalanan dinas berhasil dibuat',
                'data' => $report,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }

            Log::error('Error creating business trip report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(string $id, BusinessTripReportDTO $dto, ?UploadedFile $attachment = null): array
    {
        try {
            DB::beginTransaction();

            $report = $this->repository->findById($id);

            // Check if status is still pending
            if ($report->status !== 'pending') {
                throw new \Exception('Hanya laporan dengan status pending yang dapat diubah');
            }

            $data = $dto->toArray();

            // Handle attachment replacement
            if ($attachment) {
                if ($report->attachment_url) {
                    Storage::disk('public')->delete($report->attachment_url);
                }
                $data['attachment_url'] = $this->uploadAttachment($attachment);
            }

            $updated = $this->repository->update($id, $data);

            if (!$updated) {
                throw new \Exception('Gagal mengupdate laporan perjalanan dinas');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Laporan perjalanan dinas berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating business trip report: ' . $e->getMessage());

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

            $report = $this->repository->findById($id);

            // Check if status is still pending
            if ($report->status !== 'pending') {
                throw new \Exception('Hanya laporan dengan status pending yang dapat dihapus');
            }

            // Delete attachment if exists
            if ($report->attachment_url) {
                Storage::disk('public')->delete($report->attachment_url);
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus laporan perjalanan dinas');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Laporan perjalanan dinas berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting business trip report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function approve(string $id, string $userId, ?string $notes = null): array
    {
        try {
            DB::beginTransaction();

            $report = $this->repository->findById($id);

            if ($report->status !== 'pending') {
                throw new \Exception('Laporan sudah direview sebelumnya');
            }

            $approved = $this->repository->approve($id, $userId, $notes);

            if (!$approved) {
                throw new \Exception('Gagal menyetujui laporan perjalanan dinas');
            }

            // Update business trip status to completed
            $this->businessTripRepository->update($report->business_trip_id, [
                'status' => 'completed',
            ]);

            DB::commit();

            // TODO: Send notification to worker

            return [
                'success' => true,
                'message' => 'Laporan perjalanan dinas berhasil disetujui',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving business trip report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function reject(string $id, string $userId, string $notes): array
    {
        try {
            DB::beginTransaction();

            $report = $this->repository->findById($id);

            if ($report->status !== 'pending') {
                throw new \Exception('Laporan sudah direview sebelumnya');
            }

            $rejected = $this->repository->reject($id, $userId, $notes);

            if (!$rejected) {
                throw new \Exception('Gagal menolak laporan perjalanan dinas');
            }

            DB::commit();

            // TODO: Send notification to worker

            return [
                'success' => true,
                'message' => 'Laporan perjalanan dinas ditolak',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting business trip report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload attachment
     */
    private function uploadAttachment(UploadedFile $file): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('business-trip-reports', $filename, 'public');
    }
}
