<?php

namespace App\Http\Controllers;

use App\DTOs\BusinessTrip\BusinessTripDTO;
use App\Http\Requests\BusinessTrip\BusinessTripRequest;
use App\Services\BusinessTripService;
use App\Services\WorkerService;
use Illuminate\Http\Request;

class BusinessTripController extends Controller
{
    public function __construct(
        private readonly BusinessTripService $service,
        private readonly WorkerService $workerService
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        
        $businessTrips = $this->service->getAllPaginated(15, $filters);
        $workers = $this->workerService->getActive();
        $statistics = $this->service->getStatistics($filters);

        return view('admin.business-trip.index', compact(
            'businessTrips',
            'workers',
            'filters',
            'statistics'
        ));
    }

    public function show(string $id)
    {
        $businessTrip = $this->service->findById($id);
        return view('admin.business-trip.show', compact('businessTrip'));
    }

    public function create()
    {
        $workers = $this->workerService->getActive();
        return view('admin.business-trip.create', compact('workers'));
    }

    public function store(BusinessTripRequest $request)
    {
        $dto = BusinessTripDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.business-trip.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $businessTrip = $this->service->findById($id);
        $workers = $this->workerService->getActive();
        
        return view('admin.business-trip.edit', compact('businessTrip', 'workers'));
    }

    public function update(BusinessTripRequest $request, string $id)
    {
        $dto = BusinessTripDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.business-trip.show', $id)
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
                ->route('admin.business-trip.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function approve(string $id)
    {
        $result = $this->service->approve($id, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        $result = $this->service->reject($id, auth()->id(), $request->rejection_reason);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function pending()
    {
        $pendingTrips = $this->service->getPending();
        return view('admin.business-trip.pending', compact('pendingTrips'));
    }

    public function active()
    {
        $activeTrips = $this->service->getActive();
        return view('admin.business-trip.active', compact('activeTrips'));
    }

    public function workerSummary(Request $request, string $workerId)
    {
        $year = $request->input('year', date('Y'));
        $worker = $this->workerService->findById($workerId);
        $summary = $this->service->getWorkerSummary($workerId, $year);
        $businessTrips = $this->service->getByWorker(
            $workerId, 
            "$year-01-01", 
            "$year-12-31"
        );

        return view('admin.business-trip.worker-summary', compact(
            'worker',
            'summary',
            'businessTrips',
            'year'
        ));
    }
}
