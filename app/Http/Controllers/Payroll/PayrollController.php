<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Services\Payroll\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private readonly PayrollService $service)
    {
        $this->middleware('auth');
        $this->middleware('permission:payroll.manage|payroll.view');
    }

    public function index(Request $request)
    {
        $filters = [
            'search'   => $request->input('search'),
            'year'     => $request->input('year'),
            'status'   => $request->input('status'),
            'per_page' => $request->input('per_page', 15),
        ];

        $periods  = $this->service->getAllPeriods($filters);
        $years    = range(now()->year, now()->year - 5);

        return view('admin.payrolls.index', compact('periods', 'filters', 'years'));
    }

    public function generateForm()
    {
        $this->authorizePermission('payroll.manage');

        return view('admin.payrolls.generate');
    }

    public function generate(Request $request)
    {
        $this->authorizePermission('payroll.manage');

        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'notes'      => 'nullable|string|max:500',
        ]);

        try {
            $period = $this->service->createPeriod($validated);
            $count  = $this->service->generatePayrolls($period);

            return redirect()
                ->route('admin.payrolls.show', $period->id)
                ->with('success', "Berhasil generate penggajian untuk {$count} pegawai.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal generate penggajian: ' . $e->getMessage());
        }
    }

    public function show(Request $request, string $periodId)
    {
        $period   = $this->service->getPeriodById($periodId);
        $filters  = [
            'search'   => $request->input('search'),
            'status'   => $request->input('status'),
            'per_page' => $request->input('per_page', 20),
        ];
        $payrolls = $this->service->getPayrollsByPeriod($periodId, $filters);

        return view('admin.payrolls.show', compact('period', 'payrolls', 'filters'));
    }

    public function markPaid(string $periodId)
    {
        $this->authorizePermission('payroll.manage');

        try {
            $period = $this->service->getPeriodById($periodId);
            $this->service->markPeriodPaid($period);

            return redirect()
                ->route('admin.payrolls.show', $periodId)
                ->with('success', 'Periode penggajian telah ditandai sebagai LUNAS. Notifikasi dikirim ke semua pegawai.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function downloadSlipPdf(string $payrollId)
    {
        $payroll = $this->service->getPayrollById($payrollId);
        $pdf     = Pdf::loadView('admin.payrolls.slip-pdf', compact('payroll'));

        return $pdf->download('slip-gaji-' . $payroll->worker?->nip . '-' . ($payroll->payrollPeriod?->month_name ?? 'slip') . '.pdf');
    }

    /**
     * Helper: abort if user doesn't have the needed permission.
     */
    protected function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }
}
