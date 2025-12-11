<?php

namespace App\Http\Controllers\Master;

use App\DTOs\Master\PositionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\PositionRequest;
use App\Services\Master\PositionService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function __construct(
        private readonly PositionService $service
    ) {}

    public function index(Request $request)
    {
        $keyword = $request->input('search');
        
        $positions = $keyword 
            ? $this->service->search($keyword)
            : $this->service->getAllPaginated();

        return view('admin.master.positions.index', compact('positions', 'keyword'));
    }

    public function create()
    {
        return view('admin.master.positions.create');
    }

    public function store(PositionRequest $request)
    {
        $dto = PositionDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.positions.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function show(string $id)
    {
        $position = $this->service->findWithFileRequirements($id);
        return view('admin.master.positions.show', compact('position'));
    }

    public function edit(string $id)
    {
        $position = $this->service->findById($id);
        return view('admin.master.positions.edit', compact('position'));
    }

    public function update(PositionRequest $request, string $id)
    {
        $dto = PositionDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.positions.index')
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
                ->route('admin.master.positions.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
