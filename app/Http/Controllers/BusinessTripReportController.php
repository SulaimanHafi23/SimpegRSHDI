<?php

namespace App\Http\Controllers;

use App\DTOs\BusinessTrip\BusinessTripReportDTO;
use App\Http\Requests\BusinessTrip\BusinessTripReportRequest;
use App\Services\BusinessTripReportService;
use App\Services\BusinessTripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessTripReportController extends Controller
{
    public function __construct(
        private readonly BusinessTripReportService $service,
        private readonly BusinessTripService $businessTripService
    ) {
    }

    public function show(string $id)
    {
        $report = $this->service->findById($id);
        return view('admin.business-trip-report.show', compact('report'));
    }

    public function create(string $businessTripId)
    {
        $businessTrip = $this->businessTripService->findById($businessTripId);
        
        return view('admin.business-trip-report.create', compact('businessTrip'));
    }

    public function store(BusinessTripReportRequest $request)
    {
        $dto = BusinessTripReportDTO::fromRequest($request->validated());
        $result = $this->service->create($dto, $request->file('attachment'));

        if ($result['success']) {
            return redirect()
                ->route('admin.business-trip.show', $request->business_trip_id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $report = $this->service->findById($id);
        
        return view('admin.business-trip-report.edit', compact('report'));
    }

    public function update(BusinessTripReportRequest $request, string $id)
    {
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
        $report = $this->service->findById($id);
        $businessTripId = $report->business_trip_id;
        
        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.business-trip.show', $businessTripId)
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function approve(Request $request, string $id)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:500',
        ]);

        $result = $this->service->approve($id, auth()->id(), $request->review_notes);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'review_notes' => 'required|string|max:500',
        ], [
            'review_notes.required' => 'Catatan review harus diisi.',
        ]);

        $result = $this->service->reject($id, auth()->id(), $request->review_notes);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function downloadAttachment(string $id)
    {
        $report = $this->service->findById($id);
        
        if (!$report->attachment_url) {
            return back()->withErrors(['error' => 'File tidak ditemukan']);
        }

        if (!Storage::disk('public')->exists($report->attachment_url)) {
            return back()->withErrors(['error' => 'File tidak ditemukan di storage']);
        }

        return Storage::disk('public')->download($report->attachment_url);
    }

    public function pendingReview()
    {
        $pendingReports = $this->service->getPendingReview();
        return view('admin.business-trip-report.pending', compact('pendingReports'));
    }
}
