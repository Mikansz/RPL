<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $departments = Department::all()->keyBy('code');
        $positions = Position::all()->keyBy('code');

        $employeeData = [
            'EMP001' => ['department' => 'BOD', 'position' => 'CEO', 'salary' => 50000000],
            'EMP002' => ['department' => 'BOD', 'position' => 'CFO', 'salary' => 40000000],
            'EMP003' => ['department' => 'HR', 'position' => 'HRM', 'salary' => 20000000],
            'EMP004' => ['department' => 'HR', 'position' => 'PER', 'salary' => 9000000],
            'EMP005' => ['department' => 'IT', 'position' => 'DEV', 'salary' => 15000000],
            'EMP006' => ['department' => 'MKT', 'position' => 'MKS', 'salary' => 12000000],
            'EMP007' => ['department' => 'SALES', 'position' => 'SR', 'salary' => 10000000],
            'EMP008' => ['department' => 'ADM', 'position' => 'ADMS', 'salary' => 8000000],
        ];

        foreach ($users as $user) {
            if (isset($employeeData[$user->employee_id])) {
                $data = $employeeData[$user->employee_id];
                
                Employee::create([
                    'user_id' => $user->id,
                    'department_id' => $departments[$data['department']]->id,
                    'position_id' => $positions[$data['position']]->id,
                    'supervisor_id' => $this->getSupervisorId($user->employee_id, $users),
                    'hire_date' => now()->subYears(rand(1, 5)),
                    'employment_type' => 'permanent',
                    'employment_status' => 'active',
                    'basic_salary' => $data['salary'],
                    'bank_name' => 'Bank Mandiri',
                    'bank_account' => '1234567890' . substr($user->employee_id, -3),
                    'bank_account_name' => $user->full_name,
                    'tax_id' => '12.345.678.9-' . substr($user->employee_id, -3) . '.000',
                    'social_security_id' => '0001' . substr($user->employee_id, -7),
                ]);
            }
        }
    }

    private function getSupervisorId($employeeId, $users)
    {
        // Simple supervisor assignment logic
        $supervisors = [
            'EMP002' => 'EMP001', // CFO reports to CEO
            'EMP003' => 'EMP001', // HRM reports to CEO
            'EMP004' => 'EMP003', // Personalia reports to HRM
            'EMP005' => 'EMP001', // IT Dev reports to CEO (no IT Manager in this example)
            'EMP006' => 'EMP001', // Marketing reports to CEO
            'EMP007' => 'EMP001', // Sales reports to CEO
            'EMP008' => 'EMP003', // Admin reports to HRM
        ];

        if (isset($supervisors[$employeeId])) {
            $supervisor = $users->where('employee_id', $supervisors[$employeeId])->first();
            return $supervisor ? $supervisor->id : null;
        }

        return null;
    }
}
