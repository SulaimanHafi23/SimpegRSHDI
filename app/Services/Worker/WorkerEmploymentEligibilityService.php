<?php

namespace App\Services\Worker;

use App\Models\Attendance;
use App\Models\DocumentType;
use App\Models\Worker;
use Carbon\Carbon;

class WorkerEmploymentEligibilityService
{
    public const CATEGORY_ALL = 'all';
    public const CATEGORY_ASN = 'asn';
    public const CATEGORY_PPPK = 'pppk';
    public const CATEGORY_PPPK_PART_TIME = 'pppk_paruh_waktu';
    public const CATEGORY_NON_ASN = 'non_asn';
    public const CATEGORY_OUTSOURCED = 'outsourced';

    public const PROCESS_ONBOARDING = 'onboarding';
    public const PROCESS_PROMOTION = 'promotion';
    public const PROCESS_PAYROLL = 'payroll';
    public const PROCESS_CONTRACT_EXTENSION = 'contract_extension';

    private const STANDARD_WEEKLY_HOURS = 40;

    public function normalizeCategory(?string $category): string
    {
        $allowed = [
            self::CATEGORY_ASN,
            self::CATEGORY_PPPK,
            self::CATEGORY_PPPK_PART_TIME,
            self::CATEGORY_NON_ASN,
            self::CATEGORY_OUTSOURCED,
        ];

        if (!in_array((string) $category, $allowed, true)) {
            return self::CATEGORY_NON_ASN;
        }

        return (string) $category;
    }

    public function resolvePayrollPaymentMode(Worker $worker): string
    {
        $category = $this->normalizeCategory($worker->payroll_category);

        if ($category === self::CATEGORY_OUTSOURCED) {
            return (string) ($worker->payroll_payment_type ?: 'vendor_invoice');
        }

        return 'individual';
    }

    public function canReceivePromotion(Worker $worker): bool
    {
        $category = $this->normalizeCategory($worker->payroll_category);

        if ($category === self::CATEGORY_OUTSOURCED) {
            return false;
        }

        return $worker->status === 'active';
    }

    public function partTimeProrationRatio(Worker $worker): float
    {
        $category = $this->normalizeCategory($worker->payroll_category);

        if ($category !== self::CATEGORY_PPPK_PART_TIME) {
            return 1.0;
        }

        $weeklyHours = (int) ($worker->weekly_work_hours ?? 20);
        $weeklyHours = max(1, min(self::STANDARD_WEEKLY_HOURS, $weeklyHours));

        return round($weeklyHours / self::STANDARD_WEEKLY_HOURS, 4);
    }

    public function attendanceRatioForPeriod(Worker $worker, Carbon $startDate, Carbon $endDate): float
    {
        if ($endDate->lt($startDate)) {
            return 1.0;
        }

        $workingDays = 0;
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            if (!$cursor->isSunday()) {
                $workingDays++;
            }

            $cursor->addDay();
        }

        if ($workingDays <= 0) {
            return 1.0;
        }

        $attendedDays = Attendance::query()
            ->where('worker_id', $worker->id)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('status', ['present'])
            ->orWhere(function ($query) use ($worker, $startDate, $endDate) {
                $query->where('worker_id', $worker->id)
                    ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->where('is_late', true);
            })
            ->count();

        $ratio = $attendedDays / $workingDays;

        return round(max(0.0, min(1.0, $ratio)), 4);
    }

    public function evaluateProcess(Worker $worker, string $processType): array
    {
        $category = $this->normalizeCategory($worker->payroll_category);

        if ($worker->status !== 'active' && $processType !== self::PROCESS_ONBOARDING) {
            return [
                'eligible' => false,
                'message' => 'Pegawai tidak aktif.',
                'missing_documents' => [],
                'required_count' => 0,
                'valid_count' => 0,
            ];
        }

        $requirements = $this->getRequirements($worker, $processType);
        $requiredDocTypeIds = $requirements->pluck('required_document_type_id')->unique()->values()->all();

        if (empty($requiredDocTypeIds)) {
            return [
                'eligible' => true,
                'message' => 'Tidak ada dokumen wajib untuk proses ini.',
                'missing_documents' => [],
                'required_count' => 0,
                'valid_count' => 0,
            ];
        }

        $documents = $worker->workerDocuments()
            ->whereIn('document_type_id', $requiredDocTypeIds)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('document_type_id');

        $missing = [];
        $validCount = 0;

        foreach ($requirements as $requirement) {
            $docGroup = $documents->get($requirement['required_document_type_id']);
            $document = $docGroup?->first();
            $docName = (string) ($requirement['name'] ?? 'Dokumen');

            if (!$document) {
                $missing[] = $docName . ' belum diunggah';
                continue;
            }

            if ($document->status !== 'verified') {
                $missing[] = $docName . ' belum terverifikasi';
                continue;
            }

            if ($document->expired_date) {
                $bufferDays = (int) ($requirement['expiration_buffer_days'] ?? 0);
                $threshold = now()->addDays($bufferDays)->startOfDay();

                if ($document->expired_date->lt($threshold)) {
                    $missing[] = $docName . ' kedaluwarsa atau mendekati kedaluwarsa';
                    continue;
                }
            }

            $validCount++;
        }

        $eligible = empty($missing);
        $message = $eligible
            ? 'Seluruh dokumen wajib valid.'
            : 'Dokumen wajib belum lengkap: ' . implode('; ', array_slice($missing, 0, 3)) . (count($missing) > 3 ? ' ...' : '');

        return [
            'eligible' => $eligible,
            'message' => $message,
            'missing_documents' => $missing,
            'required_count' => count($requiredDocTypeIds),
            'valid_count' => $validCount,
        ];
    }

    private function getRequirements(Worker $worker, string $processType)
    {
        $category = $this->normalizeCategory($worker->payroll_category);

        return DocumentType::query()
            ->where('process_type', $processType)
            ->where('is_required', true)
            ->where('is_active', true)
            ->whereIn('employment_category', [self::CATEGORY_ALL, $category])
            ->get(['id', 'name', 'source_document_type_id', 'expiration_buffer_days'])
            ->map(function (DocumentType $item) {
                return [
                    'rule_document_type_id' => $item->id,
                    'required_document_type_id' => $item->source_document_type_id ?: $item->id,
                    'name' => $item->name,
                    'expiration_buffer_days' => (int) ($item->expiration_buffer_days ?? 0),
                ];
            });
    }
}
