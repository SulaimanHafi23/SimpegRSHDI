<?php
// filepath: app/Http/Controllers/Admin/Role/RoleController.php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Services\Role\RoleService;
use App\Services\Permission\PermissionService;
use App\Http\Requests\Role\RoleRequest;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService,
        protected PermissionService $permissionService
    ) {
        $this->middleware(['auth']);
        // Permission check dilakukan di blade dengan @can
    }

    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        $roles = $this->roleService->getAllPaginated($perPage);

        return view('admin.settings.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = $this->permissionService->getAll();

        return view('admin.settings.roles.create', compact('permissions'));
    }

    public function store(RoleRequest $request)
    {
        try {
            $dto = \App\DTOs\RoleDTO::fromRequest($request->validated());
            $result = $this->roleService->create($dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.roles.index')
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
            $role = $this->roleService->findById($id);
            return view('admin.settings.roles.show', compact('role'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $role = $this->roleService->findById($id);
            $permissions = $this->permissionService->getAll();

            return view('admin.settings.roles.edit', compact('role', 'permissions'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(RoleRequest $request, string $id)
    {
        try {
            $dto = \App\DTOs\RoleDTO::fromRequest($request->validated());
            $result = $this->roleService->update($id, $dto);

            if ($result['success']) {
                return redirect()
                    ->route('admin.roles.show', $id)
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
            $result = $this->roleService->delete($id);

            if ($result['success']) {
                return redirect()
                    ->route('admin.roles.index')
                    ->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
