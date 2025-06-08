<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Department;

class PositionSeeder extends Seeder
{
    public function run()
    {
        $departments = Department::all()->keyBy('code');

        $positions = [
            // Board of Directors
            ['code' => 'CEO', 'name' => 'Chief Executive Officer', 'department' => 'BOD', 'base_salary' => 50000000, 'level' => 1],
            ['code' => 'CFO', 'name' => 'Chief Financial Officer', 'department' => 'BOD', 'base_salary' => 40000000, 'level' => 2],
            
            // Finance
            ['code' => 'FM', 'name' => 'Finance Manager', 'department' => 'FIN', 'base_salary' => 25000000, 'level' => 3],
            ['code' => 'ACC', 'name' => 'Accountant', 'department' => 'FIN', 'base_salary' => 12000000, 'level' => 4],
            ['code' => 'ACCA', 'name' => 'Accounting Assistant', 'department' => 'FIN', 'base_salary' => 8000000, 'level' => 5],
            
            // Human Resources
            ['code' => 'HRM', 'name' => 'HR Manager', 'department' => 'HR', 'base_salary' => 20000000, 'level' => 3],
            ['code' => 'HRS', 'name' => 'HR Specialist', 'department' => 'HR', 'base_salary' => 12000000, 'level' => 4],
            ['code' => 'PER', 'name' => 'Personalia', 'department' => 'HR', 'base_salary' => 9000000, 'level' => 5],
            
            // Information Technology
            ['code' => 'ITM', 'name' => 'IT Manager', 'department' => 'IT', 'base_salary' => 25000000, 'level' => 3],
            ['code' => 'DEV', 'name' => 'Software Developer', 'department' => 'IT', 'base_salary' => 15000000, 'level' => 4],
            ['code' => 'SYS', 'name' => 'System Administrator', 'department' => 'IT', 'base_salary' => 12000000, 'level' => 4],
            ['code' => 'SUP', 'name' => 'IT Support', 'department' => 'IT', 'base_salary' => 8000000, 'level' => 5],
            
            // Operations
            ['code' => 'OPM', 'name' => 'Operations Manager', 'department' => 'OPS', 'base_salary' => 20000000, 'level' => 3],
            ['code' => 'OPS', 'name' => 'Operations Staff', 'department' => 'OPS', 'base_salary' => 10000000, 'level' => 4],
            
            // Marketing
            ['code' => 'MKM', 'name' => 'Marketing Manager', 'department' => 'MKT', 'base_salary' => 20000000, 'level' => 3],
            ['code' => 'MKS', 'name' => 'Marketing Specialist', 'department' => 'MKT', 'base_salary' => 12000000, 'level' => 4],
            
            // Sales
            ['code' => 'SM', 'name' => 'Sales Manager', 'department' => 'SALES', 'base_salary' => 18000000, 'level' => 3],
            ['code' => 'SR', 'name' => 'Sales Representative', 'department' => 'SALES', 'base_salary' => 10000000, 'level' => 4],
            
            // Administration
            ['code' => 'ADM', 'name' => 'Admin Manager', 'department' => 'ADM', 'base_salary' => 15000000, 'level' => 3],
            ['code' => 'ADMS', 'name' => 'Admin Staff', 'department' => 'ADM', 'base_salary' => 8000000, 'level' => 5],
        ];

        foreach ($positions as $position) {
            Position::create([
                'code' => $position['code'],
                'name' => $position['name'],
                'department_id' => $departments[$position['department']]->id,
                'base_salary' => $position['base_salary'],
                'level' => $position['level'],
                'description' => 'Posisi ' . $position['name'],
                'is_active' => true,
            ]);
        }
    }
}
