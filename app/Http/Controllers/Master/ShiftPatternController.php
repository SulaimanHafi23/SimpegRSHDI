<?php

namespace App\Http\Controllers\Master;

use App\DTOs\Master\ShiftPatternDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ShiftPatternRequest;
use App\Services\Master\ShiftPatternService;
use Illuminate\Http\Request;

class ShiftPatternController extends Controller
{
    public function __construct(
        private readonly ShiftPatternService $service
    ) {
        $this->middleware(['auth', 'role:Super Admin|HR']);
    }

    public function index(Request $request)
    {
        $keyword = $request->input('search');
        
        $shiftPatterns = $keyword 
            ? $this->service->search($keyword)
            : $this->service->getAllPaginated();

        return view('admin.settings.shift-patterns.index', compact('shiftPatterns', 'keyword'));
    }

    public function create()
    {
        $types = $this->service->getAvailableTypes();
        return view('admin.settings.shift-patterns.create', compact('types'));
    }

    public function store(ShiftPatternRequest $request)
    {
        $dto = ShiftPatternDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.settings.shift-patterns.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function show(string $id)
    {
        $shiftPattern = $this->service->findById($id);
        $typeDisplayName = $this->service->getTypeDisplayName($shiftPattern->type);
        
        return view('admin.settings.shift-patterns.show', compact('shiftPattern', 'typeDisplayName'));
    }

    public function edit(string $id)
    {
        $shiftPattern = $this->service->findById($id);
        $types = $this->service->getAvailableTypes();
        
        return view('admin.settings.shift-patterns.edit', compact('shiftPattern', 'types'));
    }

    public function update(ShiftPatternRequest $request, string $id)
    {
        $dto = ShiftPatternDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.settings.shift-patterns.index')
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
                ->route('admin.settings.shift-patterns.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
