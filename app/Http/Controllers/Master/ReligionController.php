<?php

namespace App\Http\Controllers\Admin\Master;

use App\DTOs\Master\ReligionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ReligionRequest;
use App\Services\Master\ReligionService;
use Illuminate\Http\Request;


class ReligionController extends Controller
{
    public function __construct(
        private readonly ReligionService $service
    ) {
        $this->middleware(['auth', 'role:Super Admin|HR']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->input('search');
        
        $religions = $keyword 
            ? $this->service->search($keyword)
            : $this->service->getAllPaginated();

        return view('admin.master.religions.index', compact('religions', 'keyword'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.master.religions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReligionRequest $request)
    {
        $dto = ReligionDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.religions.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $religion = $this->service->findById($id);
        return view('admin.master.religions.show', compact('religion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $religion = $this->service->findById($id);
        return view('admin.master.religions.edit', compact('religion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReligionRequest $request, string $id)
    {
        $dto = ReligionDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.religions.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.religions.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
