<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            AttendanceRuleSeeder::class,
            LeaveTypeSeeder::class,
            SalaryComponentSeeder::class,
            UserSeeder::class,
            EmployeeSeeder::class,
            PermitSeeder::class,
            OfficeShiftScheduleSeeder::class, // Add this seeder
        ]);
    }
}
