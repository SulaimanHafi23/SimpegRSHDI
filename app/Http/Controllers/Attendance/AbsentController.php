<?php

// filepath: app/Http/Controllers/AbsentController.php

namespace App\Http\Controllers\Attendance;

use App\DTOs\AbsentDTO;
use App\Http\Requests\Attendance\AbsentRequest;
use App\Services\Attendance\AbsentService;
use App\Services\Worker\WorkerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AbsentController extends Controller
{
    public function __construct(
        private readonly AbsentService $service,
        private readonly WorkerService $workerService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-attendance|view-own-attendance')->only(['index', 'show', 'workerAttendance']);
        $this->middleware('permission:create-attendance')->only(['create', 'store']);
        $this->middleware('permission:edit-attendance')->only(['edit', 'update']);
        $this->middleware('permission:delete-attendance')->only(['destroy']);
        $this->middleware('permission:view-attendance-reports')->only(['dailyReport', 'monthlyReport']);
    }

    public function index(Request $request)
    {
        $this->authorizeAnyPermission(['view-attendance', 'view-own-attendance']);

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'date' => $request->input('date'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'status' => $request->input('status'),
        ];

        // Apply permission-based filters
        $filters = $this->applyPermissionFilters($filters);

        $attendances = $this->service->getAllPaginated(15, $filters);
        
        $workers = auth()->user()->can('view-attendance') 
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);

        return view('admin.attendance.index', compact('attendances', 'workers', 'filters'));
    }

    public function show(string $id)
    {
        $this->authorizeAnyPermission(['view-attendance', 'view-own-attendance']);

        $attendance = $this->service->findById($id);

        // Check if user can only view own data
        if (auth()->user()->can('view-own-attendance') && 
            !auth()->user()->can('view-attendance') &&
            !$this->isOwnData($attendance->worker_id)) {
            abort(403, 'Anda hanya dapat melihat data kehadiran Anda sendiri.');
        }

        return view('admin.attendance.show', compact('attendance'));
    }

    public function create()
    {
        $this->authorizePermission('create-attendance');

        $workers = $this->workerService->getActive();

        return view('admin.attendance.create', compact('workers'));
    }

    public function store(AbsentRequest $request)
    {
        $this->authorizePermission('create-attendance');

        $dto = AbsentDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.attendance.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $this->authorizePermission('edit-attendance');

        $attendance = $this->service->findById($id);
        $workers = $this->workerService->getActive();

        return view('admin.attendance.edit', compact('attendance', 'workers'));
    }

    public function update(AbsentRequest $request, string $id)
    {
        $this->authorizePermission('edit-attendance');

        $dto = AbsentDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.attendance.show', $id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('delete-attendance');

        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.attendance.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function workerAttendance(Request $request, string $workerId)
    {
        $this->authorizeAnyPermission(['view-attendance', 'view-own-attendance']);

        // Check permission for viewing other worker's data
        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('view-attendance');
        }

        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $worker = $this->workerService->findById($workerId);
        $attendances = $this->service->getByWorker($workerId, $year, $month);
        $summary = $this->service->getWorkerMonthlySummary($workerId, $year, $month);

        return view('admin.attendance.worker-attendance', compact('worker', 'attendances', 'summary', 'month', 'year'));
    }

    public function dailyReport(Request $request)
    {
        $this->authorizePermission('view-attendance-reports');

        $date = $request->input('date', date('Y-m-d'));
        $report = $this->service->getDailyReport($date);

        return view('admin.attendance.daily-report', compact('report', 'date'));
    }

    public function monthlyReport(Request $request)
    {
        $this->authorizePermission('view-attendance-reports');

        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $report = $this->service->getMonthlyReport($year, $month);

        return view('admin.attendance.monthly-report', compact('report', 'month', 'year'));
    }
}
