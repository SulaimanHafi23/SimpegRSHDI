<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\ReligionService;
use App\DTOs\Master\ReligionDTO;
use Illuminate\Http\Request;

class ReligionController extends Controller
{
    public function __construct(
        protected ReligionService $religionService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:religion.view')->only(['index', 'show']);
        $this->middleware('permission:religion.create')->only(['create', 'store']);
        $this->middleware('permission:religion.edit')->only(['edit', 'update']);
        $this->middleware('permission:religion.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        $religions = $this->religionService->getAllPaginated($perPage);

        return view('admin.master.religions.index', compact('religions'));
    }

    public function create()
    {
        return view('admin.master.religions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:religions,name',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = ReligionDTO::fromRequest($validated);
            $result = $this->religionService->create($dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.religions.index')
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
            $religion = $this->religionService->findById($id);
            return view('admin.master.religions.show', compact('religion'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.religions.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $religion = $this->religionService->findById($id);
            return view('admin.master.religions.edit', compact('religion'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.religions.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:religions,name,' . $id,
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = ReligionDTO::fromRequest($validated);
            $result = $this->religionService->update($id, $dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.religions.show', $id)
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
            $result = $this->religionService->delete($id);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.religions.index')
                    ->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
