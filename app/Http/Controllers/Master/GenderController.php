<?php

namespace App\Http\Controllers\Admin\Master;

use App\DTOs\Master\GenderDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\GenderRequest;
use App\Services\Master\GenderService;
use Illuminate\Http\Request;

class GenderController extends Controller
{
    public function __construct(
        private readonly GenderService $service
    ) {
        $this->middleware(['auth', 'role:Super Admin|HR']);
    }

    public function index(Request $request)
    {
        $keyword = $request->input('search');
        
        $genders = $keyword 
            ? $this->service->search($keyword)
            : $this->service->getAllPaginated();

        return view('admin.master.genders.index', compact('genders', 'keyword'));
    }

    public function create()
    {
        return view('admin.master.genders.create');
    }

    public function store(GenderRequest $request)
    {
        $dto = GenderDTO::fromRequest($request->validated());
        $result = $this->service->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.genders.index')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function show(string $id)
    {
        $gender = $this->service->findById($id);
        return view('admin.master.genders.show', compact('gender'));
    }

    public function edit(string $id)
    {
        $gender = $this->service->findById($id);
        return view('admin.master.genders.edit', compact('gender'));
    }

    public function update(GenderRequest $request, string $id)
    {
        $dto = GenderDTO::fromRequest($request->validated());
        $result = $this->service->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.master.genders.index')
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
                ->route('admin.master.genders.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
