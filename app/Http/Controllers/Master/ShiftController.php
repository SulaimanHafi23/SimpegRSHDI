<?php

namespace App\Http\Controllers\Master;

use App\DTOs\Master\ShiftDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ShiftRequest;
use App\Services\Master\ShiftService;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(
        private readonly ShiftService $service
    ) {}

    public function index(Request $request)
    {
        $keyword = $request->input('search');
        
        $shifts = $keyword 
            ? $this->service->search($keyword)
            : $this->service->getAllPaginated();

        return view('admin.settings.shifts.index', compact('shifts', 'keyword'));
    }

    public function create()
    {
        return view('admin.settings.shifts.create');
    }

    public function store(ShiftRequest $request)
    {
        $dto = ShiftDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.settings.shifts.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function show(string $id)
    {
        $shift = $this->service->findById($id);
        $duration = $this->service->calculateShiftDuration($shift->start_time, $shift->end_time);
        
        return view('admin.settings.shifts.show', compact('shift', 'duration'));
    }

    public function edit(string $id)
    {
        $shift = $this->service->findById($id);
        return view('admin.settings.shifts.edit', compact('shift'));
    }

    public function update(ShiftRequest $request, string $id)
    {
        $dto = ShiftDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.settings.shifts.index')
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
                ->route('admin.settings.shifts.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
