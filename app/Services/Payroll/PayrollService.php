<?php

namespace App\Services\Payroll;

use App\Models\AuditLog;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Worker;
use App\Notifications\PayrollPaidNotification;
use App\Services\Worker\WorkerEmploymentEligibilityService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(
        private readonly WorkerEmploymentEligibilityService $eligibilityService
    ) {}

    public function getAllPeriods(array $filters = []): LengthAwarePaginator
    {
        $query = PayrollPeriod::query()->withCount('payrolls');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderByDesc('year')->orderByDesc('month');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getPeriodById(string $id): PayrollPeriod
    {
        return PayrollPeriod::findOrFail($id);
    }

    public function createPeriod(array $data): PayrollPeriod
    {
        return PayrollPeriod::create([
            'name'       => $data['name'],
            'month'      => $data['month'],
            'year'       => $data['year'],
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'status'     => 'draft',
            'notes'      => $data['notes'] ?? null,
        ]);
    }

    /**
     * Generate payroll entries for all active workers in a period.
     */
    public function generatePayrolls(PayrollPeriod $period): int
    {
        $workers = Worker::query()
            ->where('status', 'active')
            ->with(['salaryComponentAssignments.salaryComponent'])
            ->get();

        $count = 0;

        DB::transaction(function () use ($period, $workers, &$count) {
            $period->update(['status' => 'processing']);

            foreach ($workers as $worker) {
                $this->generateWorkerPayroll($period, $worker);
                $count++;
            }

            $period->update(['status' => 'finalized']);
        });

        return $count;
    }

    public function generateWorkerPayroll(PayrollPeriod $period, $worker): Payroll
    {
        if (!$worker instanceof Worker) {
            $worker = Worker::query()->findOrFail($worker->id ?? null);
        }

        $category = $this->eligibilityService->normalizeCategory($worker->payroll_category);
        $paymentMode = $this->eligibilityService->resolvePayrollPaymentMode($worker);

        if ($category === WorkerEmploymentEligibilityService::CATEGORY_OUTSOURCED && $paymentMode === 'vendor_invoice') {
            return Payroll::updateOrCreate(
                [
                    'payroll_period_id' => $period->id,
                    'worker_id' => $worker->id,
                ],
                [
                    'base_salary' => 0,
                    'total_earnings' => 0,
                    'total_deductions' => 0,
                    'net_salary' => 0,
                    'components' => [
                        [
                            'code' => 'VENDOR_INVOICE',
                            'name' => 'Pembayaran melalui invoice vendor',
                            'type' => 'info',
                            'calculation_type' => 'fixed',
                            'rate' => 0,
                            'amount' => 0,
                        ],
                    ],
                    'status' => 'finalized',
                    'notes' => 'Gaji individu dilewati: tenaga outsourcing dibayarkan melalui invoice vendor.',
                ]
            );
        }

        $payrollEligibility = $this->eligibilityService->evaluateProcess(
            $worker,
            WorkerEmploymentEligibilityService::PROCESS_PAYROLL
        );

        if (!$payrollEligibility['eligible']) {
            return Payroll::updateOrCreate(
                [
                    'payroll_period_id' => $period->id,
                    'worker_id' => $worker->id,
                ],
                [
                    'base_salary' => 0,
                    'total_earnings' => 0,
                    'total_deductions' => 0,
                    'net_salary' => 0,
                    'components' => [
                        [
                            'code' => 'PAYROLL_HOLD',
                            'name' => 'Payroll ditahan',
                            'type' => 'info',
                            'calculation_type' => 'fixed',
                            'rate' => 0,
                            'amount' => 0,
                            'reason' => $payrollEligibility['message'],
                        ],
                    ],
                    'status' => 'draft',
                    'notes' => $payrollEligibility['message'],
                ]
            );
        }

        $baseSalary = (float) ($worker->base_salary ?? 0);

        if ($category === WorkerEmploymentEligibilityService::CATEGORY_PPPK_PART_TIME) {
            $hourRatio = $this->eligibilityService->partTimeProrationRatio($worker);
            $attendanceRatio = $this->eligibilityService->attendanceRatioForPeriod(
                $worker,
                Carbon::parse((string) $period->start_date),
                Carbon::parse((string) $period->end_date),
            );

            $baseSalary = round($baseSalary * $hourRatio * $attendanceRatio, 2);
        }

        $earnings   = [];
        $deductions = [];

        foreach ($worker->salaryComponentAssignments as $assignment) {
            if (!$assignment->is_active || !$assignment->salaryComponent) {
                continue;
            }

            $component = $assignment->salaryComponent;
            $amount    = $assignment->computedAmount($baseSalary);

            $entry = [
                'code'             => $component->code,
                'name'             => $component->name,
                'type'             => $component->type,
                'calculation_type' => $assignment->calculation_type,
                'rate'             => (float) $assignment->amount,
                'amount'           => $amount,
            ];

            if ($component->type === 'earning') {
                $earnings[] = $entry;
            } else {
                $deductions[] = $entry;
            }
        }

        $totalEarnings   = array_sum(array_column($earnings, 'amount'));
        $totalDeductions = array_sum(array_column($deductions, 'amount'));
        $netSalary       = $baseSalary + $totalEarnings - $totalDeductions;

        if ($category === WorkerEmploymentEligibilityService::CATEGORY_PPPK_PART_TIME) {
            $earnings[] = [
                'code' => 'PART_TIME_PRORATION_INFO',
                'name' => 'Prorata PPPK Paruh Waktu',
                'type' => 'info',
                'calculation_type' => 'fixed',
                'rate' => 0,
                'amount' => 0,
                'weekly_work_hours' => (int) ($worker->weekly_work_hours ?? 20),
            ];
        }

        return Payroll::updateOrCreate(
            [
                'payroll_period_id' => $period->id,
                'worker_id'         => $worker->id,
            ],
            [
                'base_salary'      => $baseSalary,
                'total_earnings'   => $totalEarnings,
                'total_deductions' => $totalDeductions,
                'net_salary'       => $netSalary,
                'components'       => array_merge($earnings, $deductions),
                'status'           => 'draft',
            ]
        );
    }

    public function getPayrollsByPeriod(string $periodId, array $filters = []): LengthAwarePaginator
    {
        $query = Payroll::query()
            ->where('payroll_period_id', $periodId)
            ->with(['worker.department']);

        if (!empty($filters['search'])) {
            $query->whereHas('worker', fn ($q) =>
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('nip', 'like', '%' . $filters['search'] . '%')
            );
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function getPayrollById(string $id): Payroll
    {
        return Payroll::with(['payrollPeriod', 'worker.department'])->findOrFail($id);
    }

    public function getWorkerPayrolls(string $workerId, array $filters = []): LengthAwarePaginator
    {
        $query = Payroll::query()
            ->where('worker_id', $workerId)
            ->with('payrollPeriod')
            ->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 12);
    }

    public function markPeriodPaid(PayrollPeriod $period): void
    {
        DB::transaction(function () use ($period) {
            $payrolls = $period->payrolls()->where('status', '!=', 'paid')->with('worker.user')->get();

            foreach ($payrolls as $payroll) {
                $payroll->update(['status' => 'paid', 'paid_at' => now()]);

                if ($payroll->worker?->user) {
                    $payroll->worker->user->notify(new PayrollPaidNotification($payroll));
                }
            }

            $period->update(['status' => 'paid', 'paid_at' => now()]);
        });

        AuditLog::log(
            action: 'payroll_period_paid',
            description: 'Periode penggajian ' . $period->name . ' dinyatakan lunas',
            auditable: $period,
            newValues: ['paid_at' => now()->toDateString(), 'worker_count' => $period->payrolls()->count()],
        );
    }

    public function deletePeriod(PayrollPeriod $period): void
    {
        if (in_array($period->status, ['paid', 'finalized'])) {
            throw new \RuntimeException('Tidak dapat menghapus periode yang sudah finalisasi atau dibayar.');
        }

        $period->payrolls()->delete();
        $period->delete();
    }
}
