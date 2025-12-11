<?php

namespace App\Http\Controllers\BusinessTrip;

use App\DTOs\BusinessTripDTO;
use App\Http\Requests\BusinessTrip\BusinessTripRequest;
use App\Services\BusinessTrip\BusinessTripService;
use App\Services\Worker\WorkerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BusinessTripController extends Controller
{
    public function __construct(
        private readonly BusinessTripService $service,
        private readonly WorkerService $workerService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-business-trips|view-own-business-trips')->only(['index', 'show']);
        $this->middleware('permission:create-business-trips')->only(['create', 'store']);
        $this->middleware('permission:edit-business-trips')->only(['edit', 'update']);
        $this->middleware('permission:delete-business-trips')->only(['destroy']);
        $this->middleware('permission:approve-business-trips')->only(['approve']);
        $this->middleware('permission:reject-business-trips')->only(['reject']);
        $this->middleware('permission:view-pending-business-trips')->only(['pending']);
        $this->middleware('permission:view-active-business-trips')->only(['active']);
    }

    public function index(Request $request)
    {
        $this->authorizeAnyPermission(['view-business-trips', 'view-own-business-trips']);

        $filters = [
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'destination' => $request->input('destination'),
        ];

        // Apply permission-based filters
        if (auth()->user()->can('view-own-business-trips') && 
            !auth()->user()->can('view-business-trips')) {
            $filters['worker_id'] = auth()->user()->worker_id;
        }

        $businessTrips = $this->service->getAllPaginated(15, $filters);
        
        $workers = auth()->user()->can('view-business-trips')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);

        return view('admin.business-trips.index', compact('businessTrips', 'workers', 'filters'));
    }

    public function show(string $id)
    {
        $this->authorizeAnyPermission(['view-business-trips', 'view-own-business-trips']);

        $businessTrip = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-business-trips') && 
            !auth()->user()->can('view-business-trips') &&
            !$this->isOwnData($businessTrip->worker_id)) {
            abort(403, 'Anda hanya dapat melihat perjalanan dinas Anda sendiri.');
        }

        return view('admin.business-trips.show', compact('businessTrip'));
    }

    public function create()
    {
        $this->authorizePermission('create-business-trips');

        $workers = auth()->user()->can('view-business-trips')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);

        return view('admin.business-trips.create', compact('workers'));
    }

    public function store(BusinessTripRequest $request)
    {
        $this->authorizePermission('create-business-trips');

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
        $this->authorizePermission('edit-business-trips');

        $businessTrip = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-business-trips') && 
            !auth()->user()->can('edit-business-trips') &&
            !$this->isOwnData($businessTrip->worker_id)) {
            abort(403, 'Anda hanya dapat mengedit perjalanan dinas Anda sendiri.');
        }

        $workers = auth()->user()->can('view-business-trips')
            ? $this->workerService->getActive()
            : collect([auth()->user()->worker]);

        return view('admin.business-trips.edit', compact('businessTrip', 'workers'));
    }

    public function update(BusinessTripRequest $request, string $id)
    {
        $this->authorizePermission('edit-business-trips');

        $businessTrip = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-business-trips') && 
            !auth()->user()->can('edit-business-trips') &&
            !$this->isOwnData($businessTrip->worker_id)) {
            abort(403);
        }

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
        $this->authorizePermission('delete-business-trips');

        $businessTrip = $this->service->findById($id);

        // Check own data permission
        if (auth()->user()->can('view-own-business-trips') && 
            !auth()->user()->can('delete-business-trips') &&
            !$this->isOwnData($businessTrip->worker_id)) {
            abort(403);
        }

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
        $this->authorizePermission('approve-business-trips');

        $result = $this->service->approve($id, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function reject(Request $request, string $id)
    {
        $this->authorizePermission('reject-business-trips');

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
        $this->authorizePermission('view-pending-business-trips');

        $pendingTrips = $this->service->getPending();
        return view('admin.business-trips.pending', compact('pendingTrips'));
    }

    public function active()
    {
        $this->authorizePermission('view-active-business-trips');

        $activeTrips = $this->service->getActive();
        return view('admin.business-trips.active', compact('activeTrips'));
    }

    public function workerSummary(Request $request, string $workerId)
    {
        $this->authorizeAnyPermission(['view-business-trips', 'view-own-business-trips']);

        if (!$this->isOwnData($workerId)) {
            $this->authorizePermission('view-business-trips');
        }

        $year = $request->input('year', date('Y'));
        
        $worker = $this->workerService->findById($workerId);
        $summary = $this->service->getWorkerYearlySummary($workerId, $year);

        return view('admin.business-trips.worker-summary', compact('worker', 'summary', 'year'));
    }
}
