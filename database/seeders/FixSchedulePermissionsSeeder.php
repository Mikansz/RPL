<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class FixSchedulePermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create new permissions if they don't exist
        $permissions = [
            // Schedule permissions
            ['name' => 'schedules.view', 'display_name' => 'Lihat Jadwal', 'module' => 'schedules', 'description' => 'View schedules'],
            ['name' => 'schedules.create', 'display_name' => 'Buat Jadwal', 'module' => 'schedules', 'description' => 'Create schedules'],
            ['name' => 'schedules.edit', 'display_name' => 'Edit Jadwal', 'module' => 'schedules', 'description' => 'Edit schedules'],
            ['name' => 'schedules.delete', 'display_name' => 'Hapus Jadwal', 'module' => 'schedules', 'description' => 'Delete schedules'],
            ['name' => 'schedules.approve', 'display_name' => 'Approve Jadwal', 'module' => 'schedules', 'description' => 'Approve schedules'],

            // Office permissions
            ['name' => 'offices.view', 'display_name' => 'Lihat Kantor', 'module' => 'offices', 'description' => 'View offices'],
            ['name' => 'offices.create', 'display_name' => 'Buat Kantor', 'module' => 'offices', 'description' => 'Create offices'],
            ['name' => 'offices.edit', 'display_name' => 'Edit Kantor', 'module' => 'offices', 'description' => 'Edit offices'],
            ['name' => 'offices.delete', 'display_name' => 'Hapus Kantor', 'module' => 'offices', 'description' => 'Delete offices'],

            // Shift permissions
            ['name' => 'shifts.view', 'display_name' => 'Lihat Shift', 'module' => 'shifts', 'description' => 'View shifts'],
            ['name' => 'shifts.create', 'display_name' => 'Buat Shift', 'module' => 'shifts', 'description' => 'Create shifts'],
            ['name' => 'shifts.edit', 'display_name' => 'Edit Shift', 'module' => 'shifts', 'description' => 'Edit shifts'],
            ['name' => 'shifts.delete', 'display_name' => 'Hapus Shift', 'module' => 'shifts', 'description' => 'Delete shifts'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                    'module' => $permission['module'],
                    'description' => $permission['description']
                ]
            );
        }

        // Get all available roles and assign appropriate permissions
        $roles = Role::all();
        
        foreach ($roles as $role) {
            $roleName = strtolower($role->name);
            
            // Admin, CEO, HRD, HR get all permissions
            if (in_array($roleName, ['admin', 'ceo', 'hrd', 'hr'])) {
                $allPermissions = Permission::whereIn('name', array_column($permissions, 'name'))->get();
                $role->permissions()->syncWithoutDetaching($allPermissions);
                echo "Assigned all schedule permissions to role: {$role->name}\n";
            }
            // Manager gets view, create, and approve permissions
            elseif (in_array($roleName, ['manager', 'supervisor'])) {
                $managerPermissions = Permission::whereIn('name', [
                    'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.approve',
                    'offices.view', 'shifts.view'
                ])->get();
                $role->permissions()->syncWithoutDetaching($managerPermissions);
                echo "Assigned manager schedule permissions to role: {$role->name}\n";
            }
            // Karyawan/Employee gets view permission only
            elseif (in_array($roleName, ['karyawan', 'employee', 'staff'])) {
                $karyawanPermissions = Permission::whereIn('name', [
                    'schedules.view', 'offices.view', 'shifts.view'
                ])->get();
                $role->permissions()->syncWithoutDetaching($karyawanPermissions);
                echo "Assigned employee schedule permissions to role: {$role->name}\n";
            }
        }

        echo "Schedule permissions setup completed!\n";
    }
}
