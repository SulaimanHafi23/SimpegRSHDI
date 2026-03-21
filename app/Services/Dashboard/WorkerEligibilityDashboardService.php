<?php

namespace App\Services\Dashboard;

use App\Models\Worker;
use App\Models\WorkerDocument;
use App\Services\Worker\WorkerEmploymentEligibilityService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkerEligibilityDashboardService
{
    public function __construct(
        private WorkerEmploymentEligibilityService $eligibilityService
    )
    {
    }

    /**
     * Get workers blocked from promotion
     * Returns: active workers where canReceivePromotion() = false
     */
    public function getPromotionBlockedWorkers(?string $departmentId = null): Collection
    {
        $query = Worker::where('status', 'active');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $workers = $query->get();

        return $workers->filter(function ($worker) {
            return !$this->eligibilityService->canReceivePromotion($worker);
        })->map(function ($worker) {
            $result = $this->eligibilityService->evaluateProcess(
                $worker,
                WorkerEmploymentEligibilityService::PROCESS_PROMOTION
            );

            return [
                'id' => $worker->id,
                'nip' => $worker->nip,
                'name' => $worker->name,
                'category' => $worker->payroll_category,
                'department' => $worker->department?->name ?? '-',
                'reason' => $this->getPromotionBlockReason($worker),
                'message' => $result['message'] ?? 'Tidak memenuhi kriteria promosi',
            ];
        })->values();
    }

    /**
     * Get workers with payroll on hold
     * Returns: active workers missing required payroll documents
     */
    public function getPayrollHoldWorkers(?string $departmentId = null): Collection
    {
        $query = Worker::where('status', 'active');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $workers = $query->get();

        return $workers->filter(function ($worker) {
            // Skip if worker is outsourced with vendor invoice (no payroll generated)
            if ($worker->isOutsourced() && $worker->payroll_payment_type === 'vendor_invoice') {
                return false;
            }

            $result = $this->eligibilityService->evaluateProcess(
                $worker,
                WorkerEmploymentEligibilityService::PROCESS_PAYROLL
            );

            return !$result['eligible'];
        })->map(function ($worker) {
            $result = $this->eligibilityService->evaluateProcess(
                $worker,
                WorkerEmploymentEligibilityService::PROCESS_PAYROLL
            );

            return [
                'id' => $worker->id,
                'nip' => $worker->nip,
                'name' => $worker->name,
                'category' => $worker->payroll_category,
                'department' => $worker->department?->name ?? '-',
                'missing_documents' => $result['missing_documents'] ?? [],
                'required_count' => $result['required_count'] ?? 0,
                'valid_count' => $result['valid_count'] ?? 0,
                'message' => $result['message'] ?? 'Dokumen payroll tidak lengkap',
            ];
        })->values();
    }

    /**
     * Get workers with documents expiring soon
     * Returns: workers with expiration_date within specified days
     */
    public function getExpiringDocuments(?int $days = 30, ?string $departmentId = null): Collection
    {
        $expirationDate = Carbon::now()->addDays($days);

        $query = WorkerDocument::query()
            ->with(['worker', 'documentType'])
            ->whereNotNull('expired_date')
            ->whereBetween('expired_date', [
                Carbon::today(),
                $expirationDate
            ])
            ->where('status', 'verified')
            ->whereHas('worker', fn($q) => $q->where('status', 'active'));

        if ($departmentId) {
            $query->whereHas('worker', fn($q) => $q->where('department_id', $departmentId));
        }

        return $query->get()->map(function ($doc) {
            $daysUntilExpiry = $doc->expired_date->diffInDays(Carbon::today());
            $urgency = 'normal';

            if ($daysUntilExpiry <= 7) {
                $urgency = 'critical';
            } elseif ($daysUntilExpiry <= 14) {
                $urgency = 'warning';
            }

            return [
                'id' => $doc->id,
                'worker_id' => $doc->worker_id,
                'worker_name' => $doc->worker->name ?? '-',
                'worker_nip' => $doc->worker->nip ?? '-',
                'worker_category' => $doc->worker->payroll_category ?? '-',
                'department' => $doc->worker->department?->name ?? '-',
                'document_type' => $doc->documentType?->name ?? '-',
                'expired_date' => $doc->expired_date,
                'days_until_expiry' => $daysUntilExpiry,
                'urgency' => $urgency, // 'critical' (≤7), 'warning' (8-14), 'normal' (15+)
            ];
        })->values();
    }

    /**
     * Get summary statistics for eligibility dashboard
     */
    public function getEligibilitySummary(?string $departmentId = null): array
    {
        $query = Worker::where('status', 'active');
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $totalActive = $query->count();
        $promotionBlocked = $this->getPromotionBlockedWorkers($departmentId)->count();
        $payrollHold = $this->getPayrollHoldWorkers($departmentId)->count();
        $expiringDocs = $this->getExpiringDocuments(30, $departmentId)->count();
        $criticalDocs = $this->getExpiringDocuments(7, $departmentId)
            ->where('urgency', 'critical')
            ->count();

        return [
            'total_active_workers' => $totalActive,
            'promotion_blocked_count' => $promotionBlocked,
            'promotion_blocked_percentage' => $totalActive > 0 ? round(($promotionBlocked / $totalActive) * 100, 1) : 0,
            'payroll_hold_count' => $payrollHold,
            'payroll_hold_percentage' => $totalActive > 0 ? round(($payrollHold / $totalActive) * 100, 1) : 0,
            'expiring_documents_30d' => $expiringDocs,
            'critical_documents_7d' => $criticalDocs,
        ];
    }

    /**
     * Get workers by employment category
     */
    public function getWorkersByCategory(?string $departmentId = null): array
    {
        $query = Worker::where('status', 'active');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $categories = $query->selectRaw('payroll_category, COUNT(*) as count')
            ->groupBy('payroll_category')
            ->pluck('count', 'payroll_category')
            ->toArray();

        return [
            'asn' => $categories['asn'] ?? 0,
            'pppk' => $categories['pppk'] ?? 0,
            'pppk_paruh_waktu' => $categories['pppk_paruh_waktu'] ?? 0,
            'non_asn' => $categories['non_asn'] ?? 0,
            'outsourced' => $categories['outsourced'] ?? 0,
        ];
    }

    /**
     * Helper: Get human-readable reason for promotion block
     */
    private function getPromotionBlockReason(Worker $worker): string
    {
        if ($worker->isOutsourced()) {
            return 'Pegawai outsourced tidak dapat dipromosikan';
        }

        if ($worker->status !== 'active') {
            return 'Status pegawai tidak aktif';
        }

        return 'Kategori pegawai tidak memenuhi kriteria promosi';
    }

    /**
     * Get detailed eligibility check for specific worker
     */
    public function getWorkerEligibilityDetail(Worker $worker): array
    {
        $multiPromotion = $this->eligibilityService->evaluateProcess(
            $worker,
            WorkerEmploymentEligibilityService::PROCESS_PROMOTION
        );

        $payroll = $this->eligibilityService->evaluateProcess(
            $worker,
            WorkerEmploymentEligibilityService::PROCESS_PAYROLL
        );

        $onboarding = $this->eligibilityService->evaluateProcess(
            $worker,
            WorkerEmploymentEligibilityService::PROCESS_ONBOARDING
        );

        $contractExtension = $this->eligibilityService->evaluateProcess(
            $worker,
            WorkerEmploymentEligibilityService::PROCESS_CONTRACT_EXTENSION
        );

        return [
            'worker' => [
                'id' => $worker->id,
                'name' => $worker->name,
                'nip' => $worker->nip,
                'category' => $worker->payroll_category,
                'status' => $worker->status,
                'department' => $worker->department?->name,
            ],
            'processes' => [
                'promotion' => [
                    'eligible' => $multiPromotion['eligible'] && $this->eligibilityService->canReceivePromotion($worker),
                    'message' => $multiPromotion['message'],
                    'required_docs' => $multiPromotion['required_count'] ?? 0,
                    'valid_docs' => $multiPromotion['valid_count'] ?? 0,
                    'missing' => $multiPromotion['missing_documents'] ?? [],
                ],
                'payroll' => [
                    'eligible' => $payroll['eligible'],
                    'message' => $payroll['message'],
                    'required_docs' => $payroll['required_count'] ?? 0,
                    'valid_docs' => $payroll['valid_count'] ?? 0,
                    'missing' => $payroll['missing_documents'] ?? [],
                ],
                'onboarding' => [
                    'eligible' => $onboarding['eligible'],
                    'message' => $onboarding['message'],
                    'required_docs' => $onboarding['required_count'] ?? 0,
                    'valid_docs' => $onboarding['valid_count'] ?? 0,
                    'missing' => $onboarding['missing_documents'] ?? [],
                ],
                'contract_extension' => [
                    'eligible' => $contractExtension['eligible'],
                    'message' => $contractExtension['message'],
                    'required_docs' => $contractExtension['required_count'] ?? 0,
                    'valid_docs' => $contractExtension['valid_count'] ?? 0,
                    'missing' => $contractExtension['missing_documents'] ?? [],
                ],
            ],
        ];
    }
}
