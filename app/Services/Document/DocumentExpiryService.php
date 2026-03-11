<?php

namespace App\Services\Document;

use App\Models\WorkerDocument;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DocumentExpiryService
{
    /**
     * Get documents expiring within specified days.
     */
    public function getExpiringDocuments(int $days = 30): Collection
    {
        $endDate = now()->addDays($days);

        return WorkerDocument::with([
            'worker.position',
            'worker.unit',
            'documentType',
            'departmentDocumentType'
        ])
            ->whereNotNull('expired_date')
            ->whereBetween('expired_date', [now()->startOfDay(), $endDate])
            ->where('status', 'approved')
            ->orderBy('expired_date', 'asc')
            ->get();
    }

    /**
     * Get already expired documents.
     */
    public function getExpiredDocuments(): Collection
    {
        return WorkerDocument::with([
            'worker.position',
            'worker.unit',
            'documentType',
            'departmentDocumentType'
        ])
            ->whereNotNull('expired_date')
            ->where('expired_date', '<', now()->startOfDay())
            ->where('status', 'approved')
            ->orderBy('expired_date', 'desc')
            ->get();
    }

    /**
     * Get documents expiring in specific ranges (30, 60, 90 days).
     */
    public function getExpiryStatistics(): array
    {
        return [
            'expired' => $this->getExpiredDocuments()->count(),
            'expiring_30_days' => $this->getExpiringDocuments(30)->count(),
            'expiring_60_days' => $this->getExpiringDocuments(60)->count(),
            'expiring_90_days' => $this->getExpiringDocuments(90)->count(),
        ];
    }

    /**
     * Get documents grouped by urgency level.
     */
    public function getDocumentsByUrgency(): array
    {
        $now = now()->startOfDay();

        $documents = WorkerDocument::with([
            'worker.position',
            'worker.unit',
            'documentType',
            'departmentDocumentType'
        ])
            ->whereNotNull('expired_date')
            ->where('status', 'approved')
            ->get();

        return [
            'critical' => $documents->filter(function ($doc) use ($now) {
                return $doc->expired_date < $now;
            })->sortBy('expired_date'),

            'urgent' => $documents->filter(function ($doc) use ($now) {
                $daysUntilExpiry = $now->diffInDays($doc->expired_date, false);
                return $daysUntilExpiry >= 0 && $daysUntilExpiry <= 30;
            })->sortBy('expired_date'),

            'warning' => $documents->filter(function ($doc) use ($now) {
                $daysUntilExpiry = $now->diffInDays($doc->expired_date, false);
                return $daysUntilExpiry > 30 && $daysUntilExpiry <= 60;
            })->sortBy('expired_date'),

            'watch' => $documents->filter(function ($doc) use ($now) {
                $daysUntilExpiry = $now->diffInDays($doc->expired_date, false);
                return $daysUntilExpiry > 60 && $daysUntilExpiry <= 90;
            })->sortBy('expired_date'),
        ];
    }

    /**
     * Get workers with expired critical documents (STR, SIP, SIK).
     */
    public function getWorkersWithExpiredCriticalDocuments(): Collection
    {
        $criticalDocTypes = ['STR', 'SIP', 'SIK', 'Sertifikat', 'Lisensi'];

        return WorkerDocument::with([
            'worker.position',
            'worker.unit',
            'documentType',
            'departmentDocumentType'
        ])
            ->whereNotNull('expired_date')
            ->where('expired_date', '<', now()->startOfDay())
            ->where('status', 'approved')
            ->whereHas('documentType', function ($query) use ($criticalDocTypes) {
                $query->where(function ($q) use ($criticalDocTypes) {
                    foreach ($criticalDocTypes as $type) {
                        $q->orWhere('name', 'like', '%' . $type . '%');
                    }
                });
            })
            ->get()
            ->groupBy('worker_id')
            ->map(function ($docs) {
                return [
                    'worker' => $docs->first()->worker,
                    'documents' => $docs,
                    'count' => $docs->count(),
                ];
            });
    }

    /**
     * Calculate days until expiry for a document.
     */
    public function getDaysUntilExpiry(WorkerDocument $document): ?int
    {
        if (!$document->expired_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays($document->expired_date, false);
    }

    /**
     * Get urgency level for a document.
     */
    public function getUrgencyLevel(WorkerDocument $document): string
    {
        $days = $this->getDaysUntilExpiry($document);

        if ($days === null) {
            return 'none';
        }

        if ($days < 0) {
            return 'critical';
        }

        if ($days <= 30) {
            return 'urgent';
        }

        if ($days <= 60) {
            return 'warning';
        }

        if ($days <= 90) {
            return 'watch';
        }

        return 'normal';
    }

    /**
     * Get documents that need notification sent.
     * Returns documents expiring in exactly X days (30, 60, 90).
     */
    public function getDocumentsNeedingNotification(): Collection
    {
        $notificationDays = [30, 60, 90];
        $documents = collect();

        foreach ($notificationDays as $days) {
            $targetDate = now()->addDays($days)->startOfDay();

            $docs = WorkerDocument::with([
                'worker.user',
                'worker.position',
                'worker.unit',
                'documentType',
                'departmentDocumentType'
            ])
                ->whereNotNull('expired_date')
                ->whereDate('expired_date', $targetDate)
                ->where('status', 'approved')
                ->get();

            $documents = $documents->merge($docs);
        }

        // Also get documents expiring in 7 days
        $sevenDaysDate = now()->addDays(7)->startOfDay();
        $sevenDaysDocs = WorkerDocument::with([
            'worker.user',
            'worker.position',
            'worker.unit',
            'documentType',
            'departmentDocumentType'
        ])
            ->whereNotNull('expired_date')
            ->whereDate('expired_date', $sevenDaysDate)
            ->where('status', 'approved')
            ->get();

        $documents = $documents->merge($sevenDaysDocs);

        // Get documents expiring tomorrow
        $tomorrowDocs = WorkerDocument::with([
            'worker.user',
            'worker.position',
            'worker.unit',
            'documentType',
            'departmentDocumentType'
        ])
            ->whereNotNull('expired_date')
            ->whereDate('expired_date', now()->addDay()->startOfDay())
            ->where('status', 'approved')
            ->get();

        $documents = $documents->merge($tomorrowDocs);

        return $documents->unique('id');
    }
}
