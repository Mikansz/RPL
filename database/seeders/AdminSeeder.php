<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Create admin role if not exists
        $adminRole = Role::firstOrCreate([
            'name' => 'admin'
        ], [
            'display_name' => 'System Administrator',
            'description' => 'Super admin dengan akses penuh ke semua sistem dan konfigurasi',
            'is_active' => true,
        ]);

        // Give admin role all permissions
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Create admin user if not exists
        $adminUser = User::firstOrCreate([
            'employee_id' => 'ADM001'
        ], [
            'username' => 'admin',
            'email' => 'admin@stea.co.id',
            'password' => Hash::make('admin123'),
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'phone' => '081234567888',
            'gender' => 'male',
            'birth_date' => '1980-01-01',
            'address' => 'System Administrator',
            'status' => 'active',
        ]);

        // Assign admin role to user
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id => [
            'assigned_at' => now(),
            'is_active' => true,
        ]]);

        // Create employee record for admin
        $itDepartment = Department::where('code', 'IT')->first();
        $devPosition = Position::where('code', 'DEV')->first();

        if ($itDepartment && $devPosition) {
            Employee::firstOrCreate([
                'user_id' => $adminUser->id
            ], [
                'department_id' => $itDepartment->id,
                'position_id' => $devPosition->id,
                'supervisor_id' => null, // Admin has no supervisor
                'hire_date' => now()->subYears(5),
                'employment_type' => 'permanent',
                'employment_status' => 'active',
                'basic_salary' => 25000000,
                'bank_name' => 'Bank Mandiri',
                'bank_account' => '1234567890001',
                'bank_account_name' => $adminUser->full_name,
            ]);
        }

        $this->command->info('Admin user created successfully!');
        $this->command->info('Username: admin');
        $this->command->info('Password: admin123');
        $this->command->info('Email: admin@stea.co.id');
    }
}
