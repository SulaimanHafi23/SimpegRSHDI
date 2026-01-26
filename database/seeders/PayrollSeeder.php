<?php

namespace Database\Seeders;

use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\Worker;
use App\Models\SalaryComponent;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Payrolls...');

        $workers = Worker::all();
        $salaryComponents = SalaryComponent::all();

        if ($salaryComponents->isEmpty()) {
            $this->command->warn('No salary components found. Please run SalaryComponentSeeder first.');
            return;
        }

        // Generate payroll for the last 3 months
        $months = [
            Carbon::now()->subMonths(2)->format('Y-m-01'),
            Carbon::now()->subMonths(1)->format('Y-m-01'),
            Carbon::now()->format('Y-m-01'),
        ];

        foreach ($months as $periodStart) {
            $periodEnd = Carbon::parse($periodStart)->endOfMonth()->format('Y-m-d');
            
            foreach ($workers as $worker) {
                // Calculate base salary and components
                $basicSalary = $worker->salary ?? 5000000; // Default 5 juta if not set
                $grossSalary = $basicSalary;
                $totalDeductions = 0;
                $totalAllowances = 0;

                // Create payroll
                $payroll = Payroll::create([
                    'worker_id' => $worker->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'basic_salary' => $basicSalary,
                    'gross_salary' => 0, // Will be calculated after details
                    'total_deductions' => 0,
                    'net_salary' => 0,
                    'status' => 'paid',
                    'paid_at' => Carbon::parse($periodEnd)->addDays(5),
                    'notes' => null,
                ]);

                // Add salary components as payroll details
                foreach ($salaryComponents as $component) {
                    $amount = 0;
                    
                    if ($component->is_percentage) {
                        $amount = $basicSalary * ($component->amount / 100);
                    } else {
                        $amount = $component->amount;
                    }

                    // Some components are conditional
                    if ($component->type === 'allowance') {
                        // 80% chance to include allowances
                        if (rand(1, 100) > 80) continue;
                        
                        $totalAllowances += $amount;
                        $grossSalary += $amount;
                    } elseif ($component->type === 'deduction') {
                        $totalDeductions += $amount;
                    }

                    PayrollDetail::create([
                        'payroll_id' => $payroll->id,
                        'salary_component_id' => $component->id,
                        'amount' => $amount,
                        'notes' => null,
                    ]);
                }

                // Add overtime pay if exists
                $overtimeHours = rand(0, 20); // Random overtime hours
                if ($overtimeHours > 0) {
                    $overtimePay = ($basicSalary / 173) * $overtimeHours * 1.5; // 1.5x overtime rate
                    
                    PayrollDetail::create([
                        'payroll_id' => $payroll->id,
                        'salary_component_id' => null,
                        'amount' => $overtimePay,
                        'notes' => "Lembur {$overtimeHours} jam",
                    ]);
                    
                    $grossSalary += $overtimePay;
                }

                // Update payroll totals
                $netSalary = $grossSalary - $totalDeductions;
                
                $payroll->update([
                    'gross_salary' => $grossSalary,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                ]);
            }
        }

        $this->command->info('✅ Payrolls seeded successfully!');
    }
}
