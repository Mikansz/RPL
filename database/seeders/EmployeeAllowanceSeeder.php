<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\SalaryComponent;

class EmployeeAllowanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active employees
        $employees = Employee::where('employment_status', 'active')->with('user')->get();
        
        // Get default allowances
        $defaultAllowances = SalaryComponent::where('type', 'allowance')
            ->where('is_active', true)
            ->get();

        if ($employees->count() === 0) {
            $this->command->info('No active employees found. Please create employees first.');
            return;
        }

        if ($defaultAllowances->count() === 0) {
            $this->command->info('No allowances found. Running SalaryComponentSeeder first...');
            $this->call(SalaryComponentSeeder::class);
            $defaultAllowances = SalaryComponent::where('type', 'allowance')
                ->where('is_active', true)
                ->get();
        }

        $this->command->info("Assigning allowances to {$employees->count()} employees...");

        foreach ($employees as $employee) {
            $this->command->info("Processing employee: {$employee->user->full_name}");
            
            // Assign basic allowances to each employee
            foreach ($defaultAllowances as $allowance) {
                // Check if employee already has this allowance
                $existingAllowance = $employee->user->salaryComponents()
                    ->where('salary_component_id', $allowance->id)
                    ->first();

                if (!$existingAllowance) {
                    // Calculate amount based on allowance type
                    $amount = $this->calculateAllowanceAmount($allowance, $employee->basic_salary);
                    
                    $employee->user->salaryComponents()->attach($allowance->id, [
                        'amount' => $amount,
                        'effective_date' => now()->toDateString(),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->command->info("  ✅ Added {$allowance->name}: Rp " . number_format($amount, 0, ',', '.'));
                } else {
                    $this->command->info("  ⏭️  {$allowance->name} already exists");
                }
            }
        }

        $this->command->info('✅ Employee allowances seeding completed!');
    }

    private function calculateAllowanceAmount($allowance, $basicSalary)
    {
        switch ($allowance->calculation_type) {
            case 'fixed':
                return $allowance->default_amount;
            case 'percentage':
                return ($basicSalary * $allowance->percentage) / 100;
            case 'formula':
                // For formula type, use default amount as fallback
                return $allowance->default_amount;
            default:
                return 0;
        }
    }
}
