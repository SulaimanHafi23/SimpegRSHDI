<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\SalaryComponent;
use App\Models\Worker;
use App\Services\Payroll\PayrollService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-payroll')->only(['index', 'show']);
        $this->middleware('permission:create-payroll')->only(['create', 'store', 'generate']);
        $this->middleware('permission:edit-payroll')->only(['edit', 'update']);
        $this->middleware('permission:delete-payroll')->only(['destroy']);
        $this->middleware('permission:approve-payroll')->only(['approve', 'markAsPaid']);
    }

    /**
     * Display payroll list
     */
    public function index(Request $request)
    {
        $filters = [
            'period' => $request->period,
            'worker_id' => $request->worker_id,
            'status' => $request->status,
            'search' => $request->search,
            'per_page' => 20,
        ];

        $payrolls = $this->payrollService->getPayrolls($filters);
        $workers = Worker::where('status', 'active')->orderBy('name')->get();

        return view('admin.payroll.index', compact('payrolls', 'workers', 'filters'));
    }

    /**
     * Show generate payroll form
     */
    public function generate()
    {
        $workers = Worker::where('status', 'active')->orderBy('name')->get();
        $components = SalaryComponent::active()->orderBy('type')->orderBy('name')->get();

        return view('admin.payroll.generate', compact('workers', 'components'));
    }

    /**
     * Generate payroll for workers
     */
    public function processGenerate(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
            'worker_ids' => 'required|array',
            'worker_ids.*' => 'exists:workers,id',
            'basic_salaries' => 'required|array',
        ]);

        try {
            $generated = 0;
            $errors = [];

            foreach ($request->worker_ids as $index => $workerId) {
                try {
                    $worker = Worker::findOrFail($workerId);
                    
                    $salaryData = [
                        'basic_salary' => $request->basic_salaries[$workerId] ?? 0,
                        'components' => [],
                    ];

                    $this->payrollService->generatePayroll($worker, $request->period, $salaryData);
                    $generated++;
                } catch (\Exception $e) {
                    $errors[] = "Worker {$worker->name}: " . $e->getMessage();
                }
            }

            if ($generated > 0) {
                $message = "Berhasil generate {$generated} payroll";
                if (count($errors) > 0) {
                    $message .= ". Error: " . implode(', ', $errors);
                }
                return redirect()->route('admin.payroll.index', ['period' => $request->period])
                    ->with('success', $message);
            } else {
                return back()->with('error', 'Gagal generate payroll: ' . implode(', ', $errors));
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show payroll details
     */
    public function show(Payroll $payroll)
    {
        $payroll->load(['worker', 'details.salaryComponent', 'approver']);
        return view('admin.payroll.show', compact('payroll'));
    }

    /**
     * Show edit form
     */
    public function edit(Payroll $payroll)
    {
        if (!$payroll->isEditable()) {
            return redirect()->route('admin.payroll.show', $payroll)
                ->with('error', 'Payroll yang sudah disetujui tidak bisa diubah');
        }

        $payroll->load(['worker', 'details.salaryComponent']);
        $components = SalaryComponent::active()->orderBy('type')->orderBy('name')->get();

        return view('admin.payroll.edit', compact('payroll', 'components'));
    }

    /**
     * Update payroll
     */
    public function update(Request $request, Payroll $payroll)
    {
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'components' => 'nullable|array',
            'components.*.salary_component_id' => 'required|exists:salary_components,id',
            'components.*.amount' => 'required|numeric',
        ]);

        try {
            $payroll->update([
                'basic_salary' => $request->basic_salary,
            ]);

            if ($request->components) {
                $this->payrollService->updatePayrollDetails($payroll, $request->components);
            }

            return redirect()->route('admin.payroll.show', $payroll)
                ->with('success', 'Payroll berhasil diupdate');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Approve payroll
     */
    public function approve(Payroll $payroll)
    {
        try {
            $this->payrollService->approvePayroll($payroll, auth()->id());

            return redirect()->route('admin.payroll.show', $payroll)
                ->with('success', 'Payroll berhasil disetujui');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Mark payroll as paid
     */
    public function markAsPaid(Request $request, Payroll $payroll)
    {
        $request->validate([
            'payment_date' => 'required|date',
        ]);

        try {
            $this->payrollService->markAsPaid($payroll, Carbon::parse($request->payment_date));

            return redirect()->route('admin.payroll.show', $payroll)
                ->with('success', 'Payroll berhasil ditandai sebagai dibayar');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete payroll
     */
    public function destroy(Payroll $payroll)
    {
        try {
            $this->payrollService->deletePayroll($payroll);

            return redirect()->route('admin.payroll.index')
                ->with('success', 'Payroll berhasil dihapus');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}

