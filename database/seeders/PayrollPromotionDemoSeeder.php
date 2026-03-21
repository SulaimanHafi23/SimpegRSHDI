<?php

namespace Database\Seeders;

use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\PromotionRequest;
use App\Models\Worker;
use App\Models\SalaryComponent;
use App\Models\WorkerSalaryComponent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PayrollPromotionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💰 Seeding Payroll & Promotion demo data...');

        $workers = Worker::where('status', 'active')->limit(10)->get();

        if ($workers->isEmpty()) {
            $this->command->warn('⚠️ Tidak ada worker aktif. Lewati payroll demo seeding.');
            return;
        }

        // Assign payroll_category and base_salary to workers
        $categories = ['non_asn', 'asn', 'pppk', 'pppk_paruh_waktu', 'outsourced'];
        $baseSalaries = [
            'asn'        => 4500000,
            'pppk'       => 3800000,
            'non_asn'    => 3000000,
            'pppk_paruh_waktu' => 2500000,
            'outsourced' => 2800000,
        ];

        foreach ($workers as $index => $worker) {
            $category = $categories[$index % count($categories)];
            $worker->update([
                'payroll_category' => $category,
                'base_salary'      => $baseSalaries[$category] ?? 3000000,
                'rank'             => $category === 'asn' ? 'Penata' : null,
                'rank_level'       => $category === 'asn' ? 'III/c' : null,
            ]);
        }

        // Assign salary components to workers
        $salaryComponents = SalaryComponent::where('is_active', true)->get();
        if ($salaryComponents->isNotEmpty()) {
            foreach ($workers as $worker) {
                foreach ($salaryComponents as $component) {
                    WorkerSalaryComponent::firstOrCreate([
                        'worker_id'          => $worker->id,
                        'salary_component_id' => $component->id,
                    ], [
                        'calculation_type' => $component->calculation_type ?? 'fixed',
                        'amount'           => $component->default_amount ?? 0,
                        'is_active'        => true,
                    ]);
                }
            }
        }

        // Create payroll periods for last 2 months
        $periods = [];
        for ($i = 2; $i >= 1; $i--) {
            $date      = now()->subMonths($i);
            $month     = (int) $date->format('m');
            $year      = (int) $date->format('Y');
            $startDate = $date->startOfMonth()->format('Y-m-d');
            $endDate   = $date->copy()->endOfMonth()->format('Y-m-d');
            $status    = $i === 1 ? 'paid' : 'finalized';

            $period = PayrollPeriod::firstOrCreate([
                'month' => $month,
                'year'  => $year,
            ], [
                'name'       => "Gaji " . $date->translatedFormat('F Y'),
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'status'     => $status,
            ]);

            $periods[] = $period;

            // Generate payroll entries for each worker
            foreach ($workers as $worker) {
                $baseSalary = (float) ($worker->base_salary ?? 3000000);

                $earnings   = [];
                $deductions = [];
                $totalEarnings   = $baseSalary;
                $totalDeductions = 0;

                foreach ($salaryComponents as $component) {
                    $assignment = WorkerSalaryComponent::where('worker_id', $worker->id)
                        ->where('salary_component_id', $component->id)
                        ->where('is_active', true)
                        ->first();

                    if (!$assignment) {
                        continue;
                    }

                    $amount = $assignment->calculation_type === 'percentage'
                        ? ($baseSalary * $assignment->amount / 100)
                        : (float) $assignment->amount;

                    $entry = [
                        'name'   => $component->name,
                        'type'   => $component->type ?? 'earning',
                        'amount' => $amount,
                    ];

                    if (($component->type ?? 'earning') === 'earning') {
                        $earnings[]    = $entry;
                        $totalEarnings += $amount;
                    } else {
                        $deductions[]    = $entry;
                        $totalDeductions += $amount;
                    }
                }

                $netSalary = $totalEarnings - $totalDeductions;

                Payroll::firstOrCreate([
                    'payroll_period_id' => $period->id,
                    'worker_id'         => $worker->id,
                ], [
                    'base_salary'       => $baseSalary,
                    'total_earnings'    => $totalEarnings,
                    'total_deductions'  => $totalDeductions,
                    'net_salary'        => $netSalary,
                    'components'        => array_merge($earnings, $deductions),
                    'status'            => $status,
                    'paid_at'           => $status === 'paid' ? now()->subMonths($i)->endOfMonth() : null,
                ]);
            }
        }

        // Create demo promotion requests
        $asn_workers = $workers->filter(fn ($w) => $w->payroll_category === 'asn');
        foreach ($asn_workers->take(2) as $worker) {
            PromotionRequest::firstOrCreate([
                'worker_id' => $worker->id,
            ], [
                'current_rank'          => $worker->rank ?? 'Penata Muda',
                'current_rank_level'    => $worker->rank_level ?? 'III/a',
                'proposed_rank'         => 'Penata',
                'proposed_rank_level'   => 'III/c',
                'current_base_salary'   => $worker->base_salary ?? 4000000,
                'proposed_base_salary'  => ($worker->base_salary ?? 4000000) + 300000,
                'effective_date'        => now()->addMonth()->startOfMonth()->format('Y-m-d'),
                'reason'                => 'Kenaikan pangkat reguler berdasarkan masa kerja dan penilaian kinerja.',
                'status'                => 'pending',
            ]);
        }

        $done = $workers->count();
        $this->command->info("✅ Payroll demo seeding selesai. Workers: {$done}, Periods: " . count($periods));
    }
}
