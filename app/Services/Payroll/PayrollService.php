<?php

namespace App\Services\Payroll;

use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\SalaryComponent;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Generate payroll untuk worker di periode tertentu
     */
    public function generatePayroll(Worker $worker, string $period, array $salaryData = []): Payroll
    {
        DB::beginTransaction();
        try {
            // Parse period (format: YYYY-MM)
            $periodDate = Carbon::createFromFormat('Y-m', $period);
            $periodStart = $periodDate->copy()->startOfMonth();
            $periodEnd = $periodDate->copy()->endOfMonth();

            // Check if payroll already exists
            $existingPayroll = Payroll::where('worker_id', $worker->id)
                ->where('period', $period)
                ->first();

            if ($existingPayroll) {
                throw new \Exception('Payroll untuk periode ini sudah ada');
            }

            // Calculate attendance data
            $attendanceData = $this->calculateAttendance($worker->id, $periodStart, $periodEnd);
            
            // Calculate overtime
            $overtimeData = $this->calculateOvertime($worker->id, $periodStart, $periodEnd);

            // Get basic salary from salaryData or default
            $basicSalary = $salaryData['basic_salary'] ?? 0;

            // Create payroll
            $payroll = Payroll::create([
                'worker_id' => $worker->id,
                'period' => $period,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'basic_salary' => $basicSalary,
                'total_days_worked' => $attendanceData['total_days_worked'],
                'total_present' => $attendanceData['total_present'],
                'total_late' => $attendanceData['total_late'],
                'total_absent' => $attendanceData['total_absent'],
                'total_overtime_hours' => $overtimeData['total_hours'],
                'overtime_amount' => $overtimeData['total_amount'],
                'status' => 'draft',
            ]);

            // Add salary components
            $totalEarnings = $basicSalary + $overtimeData['total_amount'];
            $totalDeductions = 0;

            // Add custom components from salaryData
            if (!empty($salaryData['components'])) {
                foreach ($salaryData['components'] as $componentData) {
                    $component = SalaryComponent::find($componentData['salary_component_id']);
                    if ($component) {
                        PayrollDetail::create([
                            'payroll_id' => $payroll->id,
                            'salary_component_id' => $component->id,
                            'amount' => $componentData['amount'],
                            'description' => $componentData['description'] ?? null,
                        ]);

                        if ($component->type === 'earning') {
                            $totalEarnings += $componentData['amount'];
                        } else {
                            $totalDeductions += $componentData['amount'];
                        }
                    }
                }
            }

            // Calculate tax (simplified - bisa diperbaiki dengan rumus PPh21 yang benar)
            $taxableIncome = $totalEarnings;
            $taxAmount = $this->calculateTax($taxableIncome);
            $totalDeductions += $taxAmount;

            // Update payroll totals
            $payroll->update([
                'total_earnings' => $totalEarnings,
                'total_deductions' => $totalDeductions,
                'tax_amount' => $taxAmount,
                'gross_salary' => $totalEarnings,
                'net_salary' => $totalEarnings - $totalDeductions,
            ]);

            DB::commit();
            return $payroll;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate attendance data
     */
    private function calculateAttendance($workerId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $attendances = Attendance::where('worker_id', $workerId)
            ->whereBetween('attendance_date', [$periodStart, $periodEnd])
            ->get();

        $totalPresent = $attendances->where('status', 'present')->count();
        $totalLate = $attendances->where('is_late', true)->count();
        $totalAbsent = $attendances->where('status', 'absent')->count();

        // Calculate working days in period (exclude weekends)
        $workingDays = 0;
        $currentDate = $periodStart->copy();
        while ($currentDate <= $periodEnd) {
            if (!$currentDate->isWeekend()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return [
            'total_days_worked' => $totalPresent,
            'total_present' => $totalPresent,
            'total_late' => $totalLate,
            'total_absent' => $totalAbsent,
            'working_days' => $workingDays,
        ];
    }

    /**
     * Calculate overtime
     */
    private function calculateOvertime($workerId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $overtimes = OvertimeRequest::where('worker_id', $workerId)
            ->where('status', 'approved')
            ->whereBetween('overtime_date', [$periodStart, $periodEnd])
            ->get();

        $totalHours = $overtimes->sum('total_hours');
        
        // Rate lembur per jam (bisa disesuaikan)
        $overtimeRate = 25000; // Rp 25.000 per jam
        $totalAmount = $totalHours * $overtimeRate;

        return [
            'total_hours' => $totalHours,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Calculate tax (simplified PPh21)
     * Ini adalah kalkulasi sederhana, bisa diperbaiki dengan rumus PPh21 yang sebenarnya
     */
    private function calculateTax(float $taxableIncome): float
    {
        // PTKP (Penghasilan Tidak Kena Pajak) per bulan: Rp 4.500.000
        $ptkp = 4500000;
        
        if ($taxableIncome <= $ptkp) {
            return 0;
        }

        $taxableAmount = $taxableIncome - $ptkp;

        // Tarif pajak progresif sederhana
        if ($taxableAmount <= 5000000) {
            return $taxableAmount * 0.05; // 5%
        } elseif ($taxableAmount <= 10000000) {
            return (5000000 * 0.05) + (($taxableAmount - 5000000) * 0.15); // 15%
        } else {
            return (5000000 * 0.05) + (5000000 * 0.15) + (($taxableAmount - 10000000) * 0.25); // 25%
        }
    }

    /**
     * Approve payroll
     */
    public function approvePayroll(Payroll $payroll, $approverId): Payroll
    {
        if ($payroll->status !== 'draft') {
            throw new \Exception('Hanya payroll dengan status draft yang bisa disetujui');
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $payroll;
    }

    /**
     * Mark payroll as paid
     */
    public function markAsPaid(Payroll $payroll, Carbon $paymentDate): Payroll
    {
        if ($payroll->status !== 'approved') {
            throw new \Exception('Hanya payroll yang sudah disetujui yang bisa dibayar');
        }

        $payroll->update([
            'status' => 'paid',
            'payment_date' => $paymentDate,
        ]);

        return $payroll;
    }

    /**
     * Update payroll details
     */
    public function updatePayrollDetails(Payroll $payroll, array $components): Payroll
    {
        if (!$payroll->isEditable()) {
            throw new \Exception('Payroll yang sudah disetujui tidak bisa diubah');
        }

        DB::beginTransaction();
        try {
            // Delete existing details
            $payroll->details()->delete();

            $totalEarnings = $payroll->basic_salary + $payroll->overtime_amount;
            $totalDeductions = 0;

            // Add new details
            foreach ($components as $componentData) {
                $component = SalaryComponent::find($componentData['salary_component_id']);
                if ($component) {
                    PayrollDetail::create([
                        'payroll_id' => $payroll->id,
                        'salary_component_id' => $component->id,
                        'amount' => $componentData['amount'],
                        'description' => $componentData['description'] ?? null,
                    ]);

                    if ($component->type === 'earning') {
                        $totalEarnings += $componentData['amount'];
                    } else {
                        $totalDeductions += $componentData['amount'];
                    }
                }
            }

            // Recalculate tax
            $taxAmount = $this->calculateTax($totalEarnings);
            $totalDeductions += $taxAmount;

            // Update payroll totals
            $payroll->update([
                'total_earnings' => $totalEarnings,
                'total_deductions' => $totalDeductions,
                'tax_amount' => $taxAmount,
                'gross_salary' => $totalEarnings,
                'net_salary' => $totalEarnings - $totalDeductions,
            ]);

            DB::commit();
            return $payroll->fresh(['details.salaryComponent']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete payroll
     */
    public function deletePayroll(Payroll $payroll): bool
    {
        if (!$payroll->isEditable()) {
            throw new \Exception('Payroll yang sudah disetujui tidak bisa dihapus');
        }

        return $payroll->delete();
    }

    /**
     * Get payrolls with filters
     */
    public function getPayrolls(array $filters = [])
    {
        $query = Payroll::with(['worker', 'approver', 'details.salaryComponent']);

        if (!empty($filters['period'])) {
            $query->where('period', $filters['period']);
        }

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->whereHas('worker', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('nip', 'like', '%' . $filters['search'] . '%');
            });
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->orderBy('period', 'desc')->paginate($perPage);
    }
}
