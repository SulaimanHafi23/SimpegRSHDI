<?php

// filepath: app/Http/Controllers/BusinessTripReportController.php

namespace App\Http\Controllers\BusinessTrip;

use Illuminate\Http\Request;
use App\DTOs\BusinessTripReportDTO;
use App\Http\Controllers\Controller;
use App\Services\BusinessTrip\BusinessTripService;
use Illuminate\Support\Facades\Storage;
use App\Services\BusinessTrip\BusinessTripReportService;
use App\Http\Requests\BusinessTrip\BusinessTripReportRequest;

class BusinessTripReportController extends Controller
{
    public function __construct(
        private readonly BusinessTripReportService $service,
        private readonly BusinessTripService $businessTripService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-business-trip-reports')->only(['show', 'pendingReview']);
        $this->middleware('permission:create-business-trip-reports')->only(['create', 'store']);
        $this->middleware('permission:edit-business-trip-reports')->only(['edit', 'update']);
        $this->middleware('permission:delete-business-trip-reports')->only(['destroy']);
        $this->middleware('permission:approve-business-trip-reports')->only(['approve']);
        $this->middleware('permission:reject-business-trip-reports')->only(['reject']);
    }

    public function show(string $id)
    {
        $this->authorizePermission('view-business-trip-reports');

        $report = $this->service->findById($id);

        // Check own data permission
        if (!auth()->user()->can('view-business-trip-reports') &&
            !$this->isOwnData($report->businessTrip->worker_id)) {
            abort(403, 'Anda hanya dapat melihat laporan Anda sendiri.');
        }

        return view('admin.business-trip-reports.show', compact('report'));
    }

    public function create(string $businessTripId)
    {
        $this->authorizePermission('create-business-trip-reports');

        $businessTrip = $this->businessTripService->findById($businessTripId);

        // Check if user can create report for this trip
        if (!$this->isOwnData($businessTrip->worker_id)) {
            $this->authorizePermission('view-business-trips');
        }

        // Check if trip is approved
        if ($businessTrip->status !== 'approved') {
            return back()->withErrors(['error' => 'Hanya perjalanan dinas yang disetujui yang dapat dibuatkan laporan']);
        }

        // Check if report already exists
        if ($businessTrip->report) {
            return redirect()
                ->route('admin.business-trip-report.show', $businessTrip->report->id)
                ->with('info', 'Laporan sudah dibuat sebelumnya');
        }

        return view('admin.business-trip-reports.create', compact('businessTrip'));
    }

    public function store(BusinessTripReportRequest $request)
    {
        $this->authorizePermission('create-business-trip-reports');

        $businessTrip = $this->businessTripService->findById($request->business_trip_id);

        // Check permission
        if (!$this->isOwnData($businessTrip->worker_id)) {
            abort(403);
        }

        $dto = BusinessTripReportDTO::fromRequest($request->validated());
        $result = $this->service->create($dto, $request->file('attachment'));

        if ($result['success']) {
            return redirect()
                ->route('admin.business-trip-report.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $this->authorizePermission('edit-business-trip-reports');

        $report = $this->service->findById($id);

        // Check permission
        if (!$this->isOwnData($report->businessTrip->worker_id)) {
            abort(403, 'Anda hanya dapat mengedit laporan Anda sendiri.');
        }

        return view('admin.business-trip-reports.edit', compact('report'));
    }

    public function update(BusinessTripReportRequest $request, string $id)
    {
        $this->authorizePermission('edit-business-trip-reports');

        $report = $this->service->findById($id);

        // Check permission
        if (!$this->isOwnData($report->businessTrip->worker_id)) {
            abort(403);
        }

        $dto = BusinessTripReportDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto, $request->file('attachment'));

        if ($result['success']) {
            return redirect()
                ->route('admin.business-trip-report.show', $id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('delete-business-trip-reports');

        $report = $this->service->findById($id);

        // Check permission
        if (!$this->isOwnData($report->businessTrip->worker_id)) {
            abort(403);
        }

        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.business-trip.show', $report->business_trip_id)
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function approve(string $id)
    {
        $this->authorizePermission('approve-business-trip-reports');

        $result = $this->service->approve($id, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function reject(Request $request, string $id)
    {
        $this->authorizePermission('reject-business-trip-reports');

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

    public function pendingReview()
    {
        $this->authorizePermission('view-business-trip-reports');

        $pendingReports = $this->service->getPendingReview();
        return view('admin.business-trip-reports.pending', compact('pendingReports'));
    }

    public function downloadAttachment(string $id)
    {
        $report = $this->service->findById($id);
        
        // Check permission
        if (!$this->isOwnData($report->businessTrip->worker_id)) {
            $this->authorizePermission('view-business-trip-reports');
        }

        if (!$report->attachment_url) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        if (!Storage::disk('public')->exists($report->attachment_url)) {
            return back()->withErrors(['error' => 'File tidak ditemukan di storage']);
        }

        return Storage::disk('public')->download($report->attachment_url);
    }
}
