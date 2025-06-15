<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

        // Get all permissions
        $allPermissions = Permission::whereIn('name', array_column($permissions, 'name'))->get();

        // Give all permissions to admin roles
        $adminRoles = Role::whereIn('name', ['admin', 'ceo', 'hr', 'hrd'])->get();
        foreach ($adminRoles as $role) {
            $role->permissions()->syncWithoutDetaching($allPermissions);
        }

        // Give view permissions to manager and karyawan
        $viewPermissions = Permission::whereIn('name', ['schedules.view', 'shifts.view', 'offices.view'])->get();
        $otherRoles = Role::whereIn('name', ['manager', 'karyawan', 'personalia'])->get();
        foreach ($otherRoles as $role) {
            $role->permissions()->syncWithoutDetaching($viewPermissions);
        }

        // Give edit permissions to manager
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerPermissions = Permission::whereIn('name', [
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.approve'
            ])->get();
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions
        Permission::whereIn('name', [
            'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete', 'schedules.approve',
            'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
            'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete'
        ])->delete();
    }
};
