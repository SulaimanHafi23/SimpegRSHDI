<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display employee's payroll list
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pekerja tidak ditemukan.');
        }

        $query = Payroll::with(['details.salaryComponent'])
            ->where('worker_id', $worker->id);

        // Filter by period
        if ($request->period) {
            $query->where('period', $request->period);
        }

        // Filter by status  
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $payrolls = $query->orderBy('period', 'desc')->paginate(12);

        return view('employee.payroll.index', compact('payrolls', 'worker'));
    }

    /**
     * Show payroll details (slip gaji)
     */
    public function show(Payroll $payroll)
    {
        $user = auth()->user();
        $worker = $user->worker;

        // Check authorization
        if ($payroll->worker_id !== $worker->id) {
            abort(403, 'Unauthorized access');
        }

        $payroll->load(['worker', 'details.salaryComponent', 'approver']);

        return view('employee.payroll.show', compact('payroll'));
    }
}

