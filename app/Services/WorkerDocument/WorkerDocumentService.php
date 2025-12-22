<?php

namespace App\Services\WorkerDocument;

use App\DTOs\WorkerDocumentDTO;
use App\Repositories\Contracts\WorkerDocument\WorkerDocumentRepositoryInterface;
use App\Services\Notification\NotificationService;
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
        // Validate required fields (file already uploaded in controller)
        if (!isset($data['file_name']) || !isset($data['file_path']) || !isset($data['file_size'])) {
            throw new \Exception('File dokumen wajib diupload.');
        }

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
            $this->notificationService->notifyDocumentApproved(
                $document->worker->user_id,
                [
                    'id' => $document->id,
                    'document_type' => $document->documentType->name ?? 'Dokumen',
                ]
            );
        }
        
        return $result;
    }

    public function reject(string $id, string $verifiedBy, string $notes)
    {
        $result = $this->workerDocumentRepository->reject($id, $verifiedBy, $notes);
        
        if ($result) {
            $document = $this->workerDocumentRepository->getById($id);
            $this->notificationService->notifyDocumentRejected(
                $document->worker->user_id,
                [
                    'id' => $document->id,
                    'document_type' => $document->documentType->name ?? 'Dokumen',
                    'rejection_reason' => $notes,
                ]
            );
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

        if (!Storage::exists($document->file_path)) {
            throw new \Exception('File tidak ditemukan.');
        }

        return Storage::download($document->file_path, $document->file_name);
    }
}