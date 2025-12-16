<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\GenderService;
use App\DTOs\Master\GenderDTO;
use Illuminate\Http\Request;

class GenderController extends Controller
{
    public function __construct(
        protected GenderService $genderService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:gender.view')->only(['index', 'show']);
        $this->middleware('permission:gender.create')->only(['create', 'store']);
        $this->middleware('permission:gender.edit')->only(['edit', 'update']);
        $this->middleware('permission:gender.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        $genders = $this->genderService->getAllPaginated($perPage);

        return view('admin.master.genders.index', compact('genders'));
    }

    public function create()
    {
        return view('admin.master.genders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:genders,name',
            'code' => 'required|string|max:10|unique:genders,code',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = GenderDTO::fromRequest($validated);
            $result = $this->genderService->create($dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.genders.index')
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
            $gender = $this->genderService->findById($id);
            return view('admin.master.genders.show', compact('gender'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.genders.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $gender = $this->genderService->findById($id);
            return view('admin.master.genders.edit', compact('gender'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.master.genders.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:genders,name,' . $id,
            'code' => 'required|string|max:10|unique:genders,code,' . $id,
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $dto = GenderDTO::fromRequest($validated);
            $result = $this->genderService->update($id, $dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.genders.show', $id)
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
            $result = $this->genderService->delete($id);

            if ($result['success']) {
                return redirect()
                    ->route('admin.master.genders.index')
                    ->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}