<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class FixScheduleMenuCommand extends Command
{
    protected $signature = 'fix:schedule-menu';
    protected $description = 'Fix schedule menu permissions for all users';

    public function handle()
    {
        $this->info('🔧 Fixing Schedule Menu Permissions...');

        // Create permissions if they don't exist
        $permissions = [
            // Schedule permissions
            ['name' => 'schedules.view', 'display_name' => 'Lihat Jadwal', 'module' => 'schedules', 'description' => 'View schedules'],
            ['name' => 'schedules.create', 'display_name' => 'Buat Jadwal', 'module' => 'schedules', 'description' => 'Create schedules'],
            ['name' => 'schedules.edit', 'display_name' => 'Edit Jadwal', 'module' => 'schedules', 'description' => 'Edit schedules'],
            ['name' => 'schedules.delete', 'display_name' => 'Hapus Jadwal', 'module' => 'schedules', 'description' => 'Delete schedules'],
            ['name' => 'schedules.approve', 'display_name' => 'Setujui Jadwal', 'module' => 'schedules', 'description' => 'Approve schedules'],

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

        $this->info('✅ Permissions created/updated');

        // Get all permissions
        $allPermissions = Permission::whereIn('name', array_column($permissions, 'name'))->get();

        // Assign to Admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->info('✅ Admin role updated with schedule permissions');
        }

        // Assign to CEO role
        $ceoRole = Role::where('name', 'ceo')->first();
        if ($ceoRole) {
            $ceoRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->info('✅ CEO role updated with schedule permissions');
        }

        // Assign to HR role
        $hrRole = Role::where('name', 'hr')->first();
        if ($hrRole) {
            $hrRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->info('✅ HR role updated with schedule permissions');
        }

        // Assign to HRD role
        $hrdRole = Role::where('name', 'hrd')->first();
        if ($hrdRole) {
            $hrdRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->info('✅ HRD role updated with schedule permissions');
        }

        // Assign view permissions to Manager role
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerPermissions = Permission::whereIn('name', [
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.approve',
                'offices.view', 'shifts.view'
            ])->get();
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions);
            $this->info('✅ Manager role updated with schedule permissions');
        }

        // Assign view permissions to Karyawan role
        $karyawanRole = Role::where('name', 'karyawan')->first();
        if ($karyawanRole) {
            $karyawanPermissions = Permission::whereIn('name', [
                'schedules.view', 'shifts.view'
            ])->get();
            $karyawanRole->permissions()->syncWithoutDetaching($karyawanPermissions);
            $this->info('✅ Karyawan role updated with schedule permissions');
        }

        // Show all users and their roles
        $this->info('');
        $this->info('📋 User Information:');
        $users = User::with('roles')->get();
        foreach ($users as $user) {
            $userRoles = $user->roles->pluck('name')->toArray();
            $hasSchedulePermission = $user->hasPermission('schedules.view');
            $hasShiftPermission = $user->hasPermission('shifts.view');
            
            $this->info("   User: {$user->full_name} ({$user->username})");
            $this->info("   Roles: " . implode(', ', $userRoles));
            $this->info("   Schedule Permission: " . ($hasSchedulePermission ? '✅ Yes' : '❌ No'));
            $this->info("   Shift Permission: " . ($hasShiftPermission ? '✅ Yes' : '❌ No'));
            $this->info("   ---");
        }

        $this->info('');
        $this->info('🎉 Schedule menu permissions fixed successfully!');
        $this->info('📝 Menu items should now be visible based on user roles:');
        $this->info('   - Admin/CEO/HR/HRD: Full access to all schedule features');
        $this->info('   - Manager: Can view and manage schedules');
        $this->info('   - Karyawan: Can view schedules');
        $this->info('');
        $this->info('💡 Please refresh your browser to see the menu changes.');

        return 0;
    }
}
