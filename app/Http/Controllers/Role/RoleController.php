<?php
// filepath: app/Http/Controllers/Admin/Role/RoleController.php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Http\Requests\Role\RoleRequest;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Permission check handled by route middleware
    }

    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        $roles = Role::withCount(['permissions', 'users'])
            ->orderBy('name')
            ->paginate($perPage);

        return view('admin.settings.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('admin.settings.roles.create', compact('permissions'));
    }

    public function store(RoleRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'] ?? null,
                'description' => $validated['description'] ?? null,
                'guard_name' => config('auth.defaults.guard', 'web'),
            ]);

            if (!empty($validated['permissions'])) {
                $permissionNames = Permission::whereIn('id', $validated['permissions'])->pluck('name')->toArray();
                $role->syncPermissions($permissionNames);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            DB::commit();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Role berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating role: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $role = Role::with('permissions', 'users')->findOrFail($id);
            return view('admin.settings.roles.show', compact('role'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'Role tidak ditemukan');
        }
    }

    public function edit(string $id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);
            $permissions = Permission::orderBy('name')->get();

            return view('admin.settings.roles.edit', compact('role', 'permissions'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'Role tidak ditemukan');
        }
    }

    public function update(RoleRequest $request, string $id)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $role = Role::with('permissions', 'users')->findOrFail($id);

            if ($role->name === 'Super Admin') {
                throw new \Exception('Role Super Admin tidak dapat diubah');
            }

            $role->update([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            $permissionNames = !empty($validated['permissions'])
                ? Permission::whereIn('id', $validated['permissions'])->pluck('name')->toArray()
                : [];
            $role->syncPermissions($permissionNames);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            DB::commit();

            return redirect()
                ->route('admin.roles.show', $id)
                ->with('success', 'Role berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating role: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $role = Role::with('users')->findOrFail($id);

            if ($role->name === 'Super Admin') {
                throw new \Exception('Role Super Admin tidak dapat dihapus');
            }

            if ($role->users()->count() > 0) {
                throw new \Exception('Role masih digunakan oleh ' . $role->users()->count() . ' user');
            }

            $role->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            DB::commit();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Role berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting role: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
