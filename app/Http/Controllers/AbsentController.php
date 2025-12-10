<?php

// filepath: app/Http/Controllers/Admin/Attendance/AbsentController.php

namespace App\Http\Controllers\Admin\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AbsentRequest;
use App\Services\Attendance\AbsentService;
use App\Services\Worker\WorkerService;
use App\Services\Master\LocationService;
use Illuminate\Http\Request;

class AbsentController extends Controller
{
    public function __construct(
        private readonly AbsentService $service,
        private readonly WorkerService $workerService,
        private readonly LocationService $locationService
    ) {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->input('worker_id'),
            'location_id' => $request->input('location_id'),
            'status' => $request->input('status'),
            'date' => $request->input('date'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        
        $absents = $this->service->getAllPaginated(15, $filters);
        $workers = $this->workerService->getActive();
        $locations = $this->locationService->getAll();
        $statistics = $this->service->getStatistics(
            $filters['start_date'],
            $filters['end_date']
        );

        return view('admin.attendance.index', compact(
            'absents',
            'workers',
            'locations',
            'filters',
            'statistics'
        ));
    }

    public function show(string $id)
    {
        $absent = $this->service->findById($id);
        return view('admin.attendance.show', compact('absent'));
    }

    public function create()
    {
        $workers = $this->workerService->getActive();
        $locations = $this->locationService->getAll();
        
        return view('admin.attendance.create', compact('workers', 'locations'));
    }

    public function store(AbsentRequest $request)
    {
        $result = $this->service->checkIn(
            $request->validated(),
            $request->file('check_in_photo')
        );

        if ($result['success']) {
            return redirect()
                ->route('admin.attendance.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $absent = $this->service->findById($id);
        $workers = $this->workerService->getActive();
        $locations = $this->locationService->getAll();
        
        return view('admin.attendance.edit', compact('absent', 'workers', 'locations'));
    }

    public function update(AbsentRequest $request, string $id)
    {
        $result = $this->service->update(
            $id,
            $request->validated(),
            $request->file('check_in_photo'),
            $request->file('check_out_photo')
        );

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
        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.attendance.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Check-out
     */
    public function checkOut(Request $request, string $id)
    {
        $request->validate([
            'check_out' => 'required|date_format:H:i:s',
            'check_out_photo' => 'nullable|image|max:2048',
            'check_out_latitude' => 'nullable|numeric',
            'check_out_longitude' => 'nullable|numeric',
        ]);

        $result = $this->service->checkOut(
            $id,
            $request->all(),
            $request->file('check_out_photo')
        );

        if ($result['success']) {
            return redirect()
                ->route('admin.attendance.show', $id)
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Worker attendance history
     */
    public function workerHistory(Request $request, string $workerId)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $worker = $this->workerService->findById($workerId);
        $absents = $this->service->getByWorker($workerId, $startDate, $endDate);
        $workHours = $this->service->calculateWorkHours($workerId, $startDate, $endDate);

        return view('admin.attendance.worker-history', compact(
            'worker',
            'absents',
            'workHours',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Today's attendance report
     */
    public function todayReport()
    {
        $absents = $this->service->getTodayAbsents();
        $statistics = $this->service->getDashboardStats();

        return view('admin.attendance.today-report', compact('absents', 'statistics'));
    }
}
