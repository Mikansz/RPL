<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class FixScheduleMenuSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🔧 Fixing Schedule Menu Permissions...');

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

        $this->command->info('✅ Permissions created/updated');

        // Get all permissions
        $allPermissions = Permission::whereIn('name', array_column($permissions, 'name'))->get();

        // Assign to Admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->command->info('✅ Admin role updated with schedule permissions');
        }

        // Assign to CEO role
        $ceoRole = Role::where('name', 'ceo')->first();
        if ($ceoRole) {
            $ceoRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->command->info('✅ CEO role updated with schedule permissions');
        }

        // Assign to HR role
        $hrRole = Role::where('name', 'hr')->first();
        if ($hrRole) {
            $hrRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->command->info('✅ HR role updated with schedule permissions');
        }

        // Assign to HRD role
        $hrdRole = Role::where('name', 'hrd')->first();
        if ($hrdRole) {
            $hrdRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->command->info('✅ HRD role updated with schedule permissions');
        }

        // Assign view permissions to Manager role
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerPermissions = Permission::whereIn('name', [
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.approve',
                'offices.view', 'shifts.view'
            ])->get();
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions);
            $this->command->info('✅ Manager role updated with schedule permissions');
        }

        // Assign view permissions to Karyawan role
        $karyawanRole = Role::where('name', 'karyawan')->first();
        if ($karyawanRole) {
            $karyawanPermissions = Permission::whereIn('name', [
                'schedules.view', 'shifts.view'
            ])->get();
            $karyawanRole->permissions()->syncWithoutDetaching($karyawanPermissions);
            $this->command->info('✅ Karyawan role updated with schedule permissions');
        }

        // Give all users with admin role the permissions
        $adminUsers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'ceo']);
        })->get();

        foreach ($adminUsers as $user) {
            $this->command->info("✅ Admin user {$user->full_name} has access to schedule menus");
        }

        // Show current user info if logged in
        if (auth()->check()) {
            $currentUser = auth()->user();
            $userRoles = $currentUser->roles->pluck('name')->toArray();
            $hasSchedulePermission = $currentUser->hasPermission('schedules.view');
            $hasShiftPermission = $currentUser->hasPermission('shifts.view');
            
            $this->command->info("📋 Current User Info:");
            $this->command->info("   Name: {$currentUser->full_name}");
            $this->command->info("   Roles: " . implode(', ', $userRoles));
            $this->command->info("   Schedule Permission: " . ($hasSchedulePermission ? '✅ Yes' : '❌ No'));
            $this->command->info("   Shift Permission: " . ($hasShiftPermission ? '✅ Yes' : '❌ No'));
        }

        $this->command->info('🎉 Schedule menu permissions fixed successfully!');
        $this->command->info('📝 Menu items should now be visible based on user roles:');
        $this->command->info('   - Admin/CEO/HR/HRD: Full access to all schedule features');
        $this->command->info('   - Manager: Can view and manage schedules');
        $this->command->info('   - Karyawan: Can view schedules');
    }
}
