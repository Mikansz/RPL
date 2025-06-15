<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class DepartmentPermissionSeeder extends Seeder
{
    public function run()
    {
        // Ensure department permissions exist
        $departmentPermissions = [
            [
                'name' => 'departments.view',
                'display_name' => 'Lihat Departemen',
                'module' => 'departments',
                'description' => 'Dapat melihat daftar dan detail departemen'
            ],
            [
                'name' => 'departments.create',
                'display_name' => 'Buat Departemen',
                'module' => 'departments',
                'description' => 'Dapat membuat departemen baru'
            ],
            [
                'name' => 'departments.edit',
                'display_name' => 'Edit Departemen',
                'module' => 'departments',
                'description' => 'Dapat mengedit data departemen'
            ],
            [
                'name' => 'departments.delete',
                'display_name' => 'Hapus Departemen',
                'module' => 'departments',
                'description' => 'Dapat menghapus departemen'
            ],
        ];

        // Create permissions if they don't exist
        foreach ($departmentPermissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'display_name' => $permissionData['display_name'],
                    'module' => $permissionData['module'],
                    'description' => $permissionData['description']
                ]
            );
        }

        // Get all department permissions
        $permissions = Permission::whereIn('name', [
            'departments.view',
            'departments.create', 
            'departments.edit',
            'departments.delete'
        ])->get();

        // Assign to Admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));
            $this->command->info('✅ Admin role updated with department permissions');
        }

        // Assign to CEO role
        $ceoRole = Role::where('name', 'ceo')->first();
        if ($ceoRole) {
            $ceoRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));
            $this->command->info('✅ CEO role updated with department permissions');
        }

        // Also assign to HRD role (they should be able to manage departments)
        $hrdRole = Role::where('name', 'hrd')->first();
        if ($hrdRole) {
            $hrdRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));
            $this->command->info('✅ HRD role updated with department permissions');
        }

        // Verify admin users have the permissions
        $adminUsers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'ceo']);
        })->get();

        foreach ($adminUsers as $user) {
            $userRoles = $user->roles->pluck('name')->toArray();
            $hasPermission = $user->hasPermission('departments.edit');
            
            $this->command->info("User: {$user->full_name} ({$user->employee_id})");
            $this->command->info("Roles: " . implode(', ', $userRoles));
            $this->command->info("Can edit departments: " . ($hasPermission ? 'YES' : 'NO'));
            $this->command->info("---");
        }

        $this->command->info('🎉 Department permissions setup completed!');
    }
}
