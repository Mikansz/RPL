<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class SchedulePermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create new permissions
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

        // Assign permissions to roles - check multiple role name variations
        $adminRole = Role::whereIn('name', ['admin', 'Admin'])->first();
        $hrRole = Role::whereIn('name', ['hr', 'HR', 'hrd', 'HRD'])->first();
        $managerRole = Role::whereIn('name', ['manager', 'Manager'])->first();
        $karyawanRole = Role::whereIn('name', ['karyawan', 'employee', 'Employee'])->first();

        if ($adminRole) {
            // Admin gets all permissions
            $allPermissions = Permission::whereIn('name', array_column($permissions, 'name'))->get();
            $adminRole->permissions()->syncWithoutDetaching($allPermissions);
        }

        if ($hrRole) {
            // HR gets schedule and office management permissions
            $hrPermissions = Permission::whereIn('name', [
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.approve',
                'offices.view', 'offices.create', 'offices.edit',
                'shifts.view', 'shifts.create', 'shifts.edit'
            ])->get();
            $hrRole->permissions()->syncWithoutDetaching($hrPermissions);
        }

        if ($managerRole) {
            // Manager gets view and approve permissions
            $managerPermissions = Permission::whereIn('name', [
                'schedules.view', 'schedules.create', 'schedules.approve',
                'offices.view', 'shifts.view'
            ])->get();
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions);
        }

        if ($karyawanRole) {
            // Karyawan only gets view permission for their own schedules
            $karyawanPermissions = Permission::whereIn('name', [
                'schedules.view'
            ])->get();
            $karyawanRole->permissions()->syncWithoutDetaching($karyawanPermissions);
        }
    }
}
