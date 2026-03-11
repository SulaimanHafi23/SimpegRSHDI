<?php

namespace App\Services\WorkerDocument;

use App\DTOs\WorkerDocumentDTO;
use App\Repositories\Contracts\WorkerDocument\WorkerDocumentRepositoryInterface;
use App\Notifications\WorkerDocumentNotification;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class WorkerDocumentService
{
    public function __construct(
        protected WorkerDocumentRepositoryInterface $workerDocumentRepository,
        protected NotificationService $notificationService
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->workerDocumentRepository->getAll($filters);
    }

    public function getAllPaginated(int $perPage = 15, array $filters = [])
    {
        $filters['per_page'] = $perPage;
        return $this->getAll($filters);
    }

    public function getById(string $id)
    {
        return $this->workerDocumentRepository->getById($id);
    }

    public function getByWorkerId(string $workerId)
    {
        return $this->workerDocumentRepository->getByWorkerId($workerId);
    }

    public function create(array $data)
    {
        if (!isset($data['file'])) {
            throw new \Exception('File is required.');
        }

        $file = $data['file'];
        $workerId = $data['worker_id'];

        // If department_document_type_id provided, resolve document_type_id
        if (!empty($data['department_document_type_id'])) {
            $ddt = \App\Models\DepartmentDocumentType::find($data['department_document_type_id']);
            if (! $ddt) {
                throw new \Exception('Tipe dokumen untuk departemen tidak ditemukan');
            }
            // ensure the base document_type_id is set for backward compatibility
            $data['document_type_id'] = $ddt->document_type_id;
        }

        // Save file
        $filename = sprintf(
            '%s_%s_%s.%s',
            $workerId,
            $data['document_type_id'] ?? 'unknown',
            now()->format('YmdHis'),
            $file->getClientOriginalExtension()
        );

        $filePath = $file->storeAs('worker-documents', $filename, 'public');

        $data['file_name'] = $file->getClientOriginalName();
        $data['file_path'] = $filePath;
        $data['file_size'] = $file->getSize();
        $data['status'] = 'pending';

        $dto = WorkerDocumentDTO::fromRequest($data);
        return $this->workerDocumentRepository->create($dto);
    }

    public function update(string $id, array $data)
    {
        $document = $this->workerDocumentRepository->getById($id);

        if (isset($data['file'])) {
            // Delete old file
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Save new file
            $file = $data['file'];
            $filename = sprintf(
                '%s_%s_%s.%s',
                $document->worker_id,
                $document->document_type_id,
                now()->format('YmdHis'),
                $file->getClientOriginalExtension()
            );

            $filePath = $file->storeAs('worker-documents', $filename, 'public');

            $data['file_name'] = $file->getClientOriginalName();
            $data['file_path'] = $filePath;
            $data['file_size'] = $file->getSize();
            $data['status'] = 'pending';
        }

        $dto = WorkerDocumentDTO::fromRequest($data);
        return $this->workerDocumentRepository->update($id, $dto);
    }

    public function delete(string $id): bool
    {
        return $this->workerDocumentRepository->delete($id);
    }

    public function verify(string $id, string $verifiedBy, ?string $notes = null)
    {
        $result = $this->workerDocumentRepository->verify($id, $verifiedBy, $notes);

        if ($result) {
            $document = $this->workerDocumentRepository->getById($id);
            $user = \App\Models\User::where('worker_id', $document->worker_id)->first();
            if ($user) {
                // Kirim email notifikasi ke pegawai
                Notification::send($user, new WorkerDocumentNotification($document, 'verified'));
                // Simpan ke custom notifications table untuk dashboard
                $this->notificationService->notifyDocumentVerified(
                    $user->id,
                    [
                        'id' => $document->id,
                        'document_type' => $document->documentType?->name ?? 'Dokumen',
                    ]
                );
            }
        }

        return $result;
    }

    public function reject(string $id, string $verifiedBy, string $notes)
    {
        $result = $this->workerDocumentRepository->reject($id, $verifiedBy, $notes);

        if ($result) {
            $document = $this->workerDocumentRepository->getById($id);
            $user = \App\Models\User::where('worker_id', $document->worker_id)->first();
            if ($user) {
                // Kirim email notifikasi ke pegawai
                Notification::send($user, new WorkerDocumentNotification($document, 'rejected', $notes));
                // Simpan ke custom notifications table untuk dashboard
                $this->notificationService->notifyDocumentRejected(
                    $user->id,
                    [
                        'id' => $document->id,
                        'document_type' => $document->documentType?->name ?? 'Dokumen',
                        'rejection_reason' => $notes,
                    ]
                );
            }
        }

        return $result;
    }

    public function getExpiredDocuments()
    {
        return $this->workerDocumentRepository->getExpiredDocuments();
    }

    public function getExpiringDocuments(int $days = 30)
    {
        return $this->workerDocumentRepository->getExpiringDocuments($days);
    }

    public function downloadDocument(string $id)
    {
        $document = $this->workerDocumentRepository->getById($id);

        // Prefer public disk (files are stored using the 'public' disk).
        $disk = Storage::disk('public');

        if (!$document || !$document->file_path) {
            throw new \Exception('Dokumen tidak ditemukan.');
        }

        if (!$disk->exists($document->file_path)) {
            // Try default disk as fallback
            if (!Storage::exists($document->file_path)) {
                throw new \Exception('File not found.');
            }
            return response()->download(Storage::path($document->file_path), $document->file_name);
        }

        return response()->download($disk->path($document->file_path), $document->file_name);
    }
}
