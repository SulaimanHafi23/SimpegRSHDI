<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\LocationService;
use App\DTOs\Master\LocationDTO;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        protected LocationService $locationService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:location.view')->only(['index', 'show']);
        $this->middleware('permission:location.create')->only(['create', 'store']);
        $this->middleware('permission:location.edit')->only(['edit', 'update']);
        $this->middleware('permission:location.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'is_active' => $request->is_active,
            'per_page' => $request->per_page ?? 15,
        ];

        $locations = $this->locationService->getAll($filters);

        return view('admin.master.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.master.locations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = LocationDTO::fromRequest($validated);
            $result = $this->locationService->create($dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.locations.index')
                    ->with('success', $result['message']);
            }

            return back()
                ->withInput()
                ->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $location = $this->locationService->findById($id);
            return view('admin.master.locations.show', compact('location'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.locations.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $location = $this->locationService->findById($id);
            return view('admin.master.locations.edit', compact('location'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.locations.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = LocationDTO::fromRequest($validated);
            $result = $this->locationService->update($id, $dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.locations.show', $id)
                    ->with('success', $result['message']);
            }

            return back()
                ->withInput()
                ->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $result = $this->locationService->delete($id);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.locations.index')
                    ->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
