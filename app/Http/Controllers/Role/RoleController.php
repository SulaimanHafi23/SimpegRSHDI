<?php
// filepath: app/Http/Controllers/Admin/Role/RoleController.php

namespace App\Http\Controllers\Role;

use App\DTOs\RoleDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Services\Role\RoleService;
use App\Services\Permission\PermissionService;
use Illuminate\Http\Request;

/**
 * @method void middleware(string|array $middleware)
 */
class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly PermissionService $permissionService
    ) {
        $this->middleware(['auth']);
        $this->middleware('permission:manage-roles');
    }

    public function index()
    {
        $roles = $this->roleService->getAllPaginated(15);
        return view('admin.roles.index', compact('roles'));
    }

    public function show(string $id)
    {
        $role = $this->roleService->findById($id);
        return view('admin.roles.show', compact('role'));
    }

    public function create()
    {
        $permissions = $this->permissionService->getGrouped();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(RoleRequest $request)
    {
        $dto = RoleDTO::fromRequest($request->validated());
        $result = $this->roleService->create($dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.roles.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $role = $this->roleService->findById($id);
        $permissions = $this->permissionService->getGrouped();
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(RoleRequest $request, string $id)
    {
        $dto = RoleDTO::fromRequest($request->validated());
        $result = $this->roleService->update($id, $dto);

        if ($result['success']) {
            return redirect()
                ->route('admin.roles.show', $id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function destroy(string $id)
    {
        $result = $this->roleService->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.roles.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
