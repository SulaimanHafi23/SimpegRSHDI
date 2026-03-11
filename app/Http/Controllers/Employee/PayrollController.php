<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Payroll\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function __construct(private readonly PayrollService $service)
    {
        $this->middleware('auth');
        $this->middleware('permission:payroll.view');
    }

    public function index(Request $request)
    {
        $worker = Auth::user()?->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        $filters  = ['per_page' => $request->input('per_page', 12)];
        $payrolls = $this->service->getWorkerPayrolls($worker->id, $filters);

        return view('employee.payrolls.index', compact('payrolls', 'worker'));
    }

    public function show(string $id)
    {
        $payroll = $this->service->getPayrollById($id);
        $worker  = Auth::user()?->worker;

        // Ensure employee can only see their own payroll
        if (!$worker || $payroll->worker_id !== $worker->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        return view('employee.payrolls.show', compact('payroll', 'worker'));
    }

    public function downloadSlipPdf(string $id)
    {
        $payroll = $this->service->getPayrollById($id);
        $worker  = Auth::user()?->worker;

        if (!$worker || $payroll->worker_id !== $worker->id) {
            abort(403);
        }

        $pdf = Pdf::loadView('admin.payrolls.slip-pdf', compact('payroll'));

        return $pdf->download('slip-gaji-' . $worker->nip . '-' . ($payroll->payrollPeriod?->month_name ?? 'slip') . '.pdf');
    }
}
