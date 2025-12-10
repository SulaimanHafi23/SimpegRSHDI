<?php

namespace App\Http\Controllers\Admin\Master;

use App\DTOs\Master\LocationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\LocationRequest;
use App\Services\Master\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        private readonly LocationService $service
    ) {
        $this->middleware(['auth', 'role:Super Admin|HR']);
    }

    public function index(Request $request)
    {
        $keyword = $request->input('search');
        
        $locations = $keyword 
            ? $this->service->search($keyword)
            : $this->service->getAllPaginated();

        return view('admin.master.locations.index', compact('locations', 'keyword'));
    }

    public function create()
    {
        return view('admin.master.locations.create');
    }

    public function store(LocationRequest $request)
    {
        $dto = LocationDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.locations.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function show(string $id)
    {
        $location = $this->service->findById($id);
        return view('admin.master.locations.show', compact('location'));
    }

    public function edit(string $id)
    {
        $location = $this->service->findById($id);
        return view('admin.master.locations.edit', compact('location'));
    }

    public function update(LocationRequest $request, string $id)
    {
        $dto = LocationDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.locations.index')
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
                ->route('admin.master.locations.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
