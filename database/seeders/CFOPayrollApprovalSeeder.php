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

class CFOPayrollApprovalSeeder extends Seeder
{
    public function run()
    {
        // Ensure CFO role exists and has payroll.approve permission
        $cfoRole = Role::where('name', 'cfo')->first();
        
        if (!$cfoRole) {
            $cfoRole = Role::create([
                'name' => 'cfo',
                'display_name' => 'Chief Financial Officer',
                'description' => 'Akses ke laporan keuangan, approval gaji, dan budget',
                'is_active' => true,
            ]);
        }

        // Ensure payroll.approve permission exists
        $approvePermission = Permission::where('name', 'payroll.approve')->first();
        
        if (!$approvePermission) {
            $approvePermission = Permission::create([
                'name' => 'payroll.approve',
                'display_name' => 'Approve Payroll',
                'module' => 'payroll',
                'description' => 'Approve payroll for employees'
            ]);
        }

        // Give CFO role the payroll.approve permission
        if (!$cfoRole->hasPermission('payroll.approve')) {
            $cfoRole->givePermissionTo($approvePermission);
            $this->command->info('✅ CFO role granted payroll.approve permission');
        }

        // Ensure CFO has other necessary permissions
        $cfoPermissions = [
            'dashboard.view', 'dashboard.analytics',
            'employees.view', 'employees.view_salary',
            'payroll.view_all', 'payroll.approve', 'payroll.reports',
            'reports.view', 'reports.financial', 'reports.export',
            'attendance.reports',
        ];

        $permissions = Permission::whereIn('name', $cfoPermissions)->get();
        $cfoRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));

        // Create a test CFO user if it doesn't exist
        $cfoUser = User::where('employee_id', 'CFO001')->first();
        
        if (!$cfoUser) {
            $cfoUser = User::create([
                'employee_id' => 'CFO001',
                'username' => 'cfo',
                'email' => 'cfo@stea.co.id',
                'password' => Hash::make('cfo123'),
                'first_name' => 'Chief Financial',
                'last_name' => 'Officer',
                'phone' => '081234567890',
                'gender' => 'male',
                'birth_date' => '1975-01-01',
                'address' => 'Jakarta',
                'status' => 'active',
            ]);

            // Assign CFO role to user
            $cfoUser->roles()->attach($cfoRole->id, [
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            // Create employee record
            $department = Department::where('code', 'BOD')->first();
            $position = Position::where('code', 'CFO')->first();

            if ($department && $position) {
                Employee::create([
                    'user_id' => $cfoUser->id,
                    'department_id' => $department->id,
                    'position_id' => $position->id,
                    'hire_date' => now()->subYears(2),
                    'employment_status' => 'active',
                    'employment_type' => 'permanent',
                    'basic_salary' => 40000000,
                ]);
            }

            $this->command->info('✅ CFO test user created: cfo@stea.co.id / cfo123');
        } else {
            // Ensure existing CFO user has the role
            if (!$cfoUser->hasRole('cfo')) {
                $cfoUser->roles()->attach($cfoRole->id, [
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);
                $this->command->info('✅ CFO role assigned to existing user');
            }
        }

        $this->command->info('✅ CFO payroll approval setup completed');
    }
}
