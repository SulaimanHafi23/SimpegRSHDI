<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MasterDataPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define master data modules
        $modules = [
            'department' => 'Departemen',
            'shift' => 'Shift',
            'leave-type' => 'Tipe Cuti',
            'document-type' => 'Tipe Dokumen',
        ];

        // Define actions
        $actions = ['view', 'create', 'edit', 'delete'];

        $permissions = [];

        // Create permissions for each module
        foreach ($modules as $module => $label) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";

                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName],
                    ['guard_name' => 'web']
                );

                $permissions[] = $permission;

                $this->command->info("Permission created: {$permissionName}");
            }
        }

        // Assign all permissions to Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions($permissions);
            $this->command->info("✓ All master data permissions assigned to Super Admin role");
        }

        // Assign view permissions to HR
        $hr = Role::where('name', 'HR')->first();
        if ($hr) {
            $editPermissions = Permission::whereIn('name', [
                'department.view', 'department.create', 'department.edit',
                'shift.view', 'shift.create', 'shift.edit',
                'leave-type.view', 'leave-type.create', 'leave-type.edit',
                'document-type.view', 'document-type.create', 'document-type.edit',
            ])->get();

            $hr->givePermissionTo($editPermissions);
            $this->command->info("✓ Master data permissions assigned to HR role");
        }

        // Manager only gets view permissions
        $manager = Role::where('name', 'Manager')->first();
        if ($manager) {
            $viewPermissions = Permission::where('name', 'like', '%.view')->get();
            $manager->givePermissionTo($viewPermissions);
            $this->command->info("✓ View permissions assigned to Manager role");
        }

        $this->command->info("✓ Master Data permissions seeded successfully!");
        $this->command->info("✓ Total permissions created: " . count($permissions));
    }
}
