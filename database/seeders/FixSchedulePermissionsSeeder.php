<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class FixSchedulePermissionsSeeder extends Seeder
{
    public function run()
    {
        // Get all roles
        $adminRole = Role::where('name', 'admin')->first();
        $hrRole = Role::where('name', 'hr')->first();
        $hrdRole = Role::where('name', 'hrd')->first();
        $ceoRole = Role::where('name', 'ceo')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $karyawanRole = Role::where('name', 'karyawan')->first();

        // Define permissions for each role
        $adminPermissions = [
            'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete', 'schedules.approve',
            'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
            'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete'
        ];

        $hrPermissions = [
            'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete', 'schedules.approve',
            'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
            'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete'
        ];

        $managerPermissions = [
            'schedules.view', 'schedules.approve',
            'offices.view', 'shifts.view'
        ];

        $karyawanPermissions = [
            'schedules.view' // Only view their own schedules
        ];

        // Apply permissions to roles
        if ($adminRole) {
            $permissions = Permission::whereIn('name', $adminPermissions)->get();
            $adminRole->permissions()->sync($permissions);
            $this->command->info("Updated admin permissions");
        }

        if ($hrRole) {
            $permissions = Permission::whereIn('name', $hrPermissions)->get();
            $hrRole->permissions()->sync($permissions);
            $this->command->info("Updated HR permissions");
        }

        if ($hrdRole) {
            $permissions = Permission::whereIn('name', $hrPermissions)->get();
            $hrdRole->permissions()->sync($permissions);
            $this->command->info("Updated HRD permissions");
        }

        if ($ceoRole) {
            $permissions = Permission::whereIn('name', $adminPermissions)->get();
            $ceoRole->permissions()->sync($permissions);
            $this->command->info("Updated CEO permissions");
        }

        if ($managerRole) {
            $permissions = Permission::whereIn('name', $managerPermissions)->get();
            $managerRole->permissions()->sync($permissions);
            $this->command->info("Updated Manager permissions");
        }

        if ($karyawanRole) {
            $permissions = Permission::whereIn('name', $karyawanPermissions)->get();
            $karyawanRole->permissions()->sync($permissions);
            $this->command->info("Updated Karyawan permissions - only view schedules");
        }

        $this->command->info("Schedule permissions have been properly configured!");
    }
}
