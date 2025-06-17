<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class SalaryComponentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔐 Setting up Salary Component Permissions...');

        // Define salary component permissions
        $permissions = [
            'salary_components.view' => 'View salary components',
            'salary_components.create' => 'Create new salary components',
            'salary_components.edit' => 'Edit salary components',
            'salary_components.delete' => 'Delete salary components',
            'salary_components.manage' => 'Full salary component management',
        ];

        // Create permissions
        $this->command->info('📝 Creating permissions...');
        $createdPermissions = [];
        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => ucwords(str_replace(['_', '.'], ' ', $name)),
                    'description' => $description,
                    'module' => 'salary_components'
                ]
            );
            $createdPermissions[] = $permission;
            $this->command->info("  ✅ {$name}");
        }

        // Setup Admin role (full access)
        $this->command->info('👑 Setting up Admin role...');
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full system access',
                'is_active' => true
            ]
        );

        $adminPermissions = Permission::whereIn('name', array_keys($permissions))->get();
        $adminRole->permissions()->syncWithoutDetaching($adminPermissions->pluck('id'));
        $this->command->info('  ✅ Admin granted all salary component permissions');

        // Setup HRD role
        $this->command->info('👥 Setting up HRD role...');
        $hrdRole = Role::firstOrCreate(
            ['name' => 'HRD'],
            [
                'display_name' => 'Human Resources Department',
                'description' => 'HR management access',
                'is_active' => true
            ]
        );

        $hrdPermissions = Permission::whereIn('name', [
            'salary_components.view',
            'salary_components.create',
            'salary_components.edit',
            'salary_components.manage'
        ])->get();
        $hrdRole->permissions()->syncWithoutDetaching($hrdPermissions->pluck('id'));
        $this->command->info('  ✅ HRD granted salary component management permissions');

        // Setup CFO role
        $this->command->info('💰 Setting up CFO role...');
        $cfoRole = Role::firstOrCreate(
            ['name' => 'CFO'],
            [
                'display_name' => 'Chief Financial Officer',
                'description' => 'Financial management access',
                'is_active' => true
            ]
        );

        $cfoPermissions = Permission::whereIn('name', [
            'salary_components.view',
            'salary_components.edit',
            'salary_components.manage'
        ])->get();
        $cfoRole->permissions()->syncWithoutDetaching($cfoPermissions->pluck('id'));
        $this->command->info('  ✅ CFO granted salary component edit permissions');

        // Setup HR role (alternative name for HRD)
        $this->command->info('👥 Setting up HR role...');
        $hrRole = Role::firstOrCreate(
            ['name' => 'HR'],
            [
                'display_name' => 'Human Resources',
                'description' => 'HR management access',
                'is_active' => true
            ]
        );

        $hrRole->permissions()->syncWithoutDetaching($hrdPermissions->pluck('id'));
        $this->command->info('  ✅ HR granted salary component management permissions');

        // Show summary
        $this->command->info('');
        $this->command->info('📊 Permission Summary:');
        $this->command->info('  Admin: Full access (view, create, edit, delete, manage)');
        $this->command->info('  HRD/HR: Management access (view, create, edit, manage)');
        $this->command->info('  CFO: Edit access (view, edit, manage)');

        // Show users with access
        $this->command->info('');
        $this->command->info('👤 Users with Salary Component Access:');
        $usersWithAccess = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['Admin', 'HRD', 'HR', 'CFO']);
        })->with('roles')->get();

        foreach ($usersWithAccess as $user) {
            $roleNames = $user->roles->pluck('display_name')->join(', ');
            $this->command->info("  - {$user->full_name} ({$user->email}) - {$roleNames}");
        }

        $this->command->info('');
        $this->command->info('✅ Salary Component Permissions setup completed!');
    }
}
