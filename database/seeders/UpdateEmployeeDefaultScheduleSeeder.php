<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Office;

class UpdateEmployeeDefaultScheduleSeeder extends Seeder
{
    public function run()
    {
        // Get default shift and office
        $defaultShift = Shift::where('is_active', true)->first();
        $defaultOffice = Office::where('is_active', true)->first();

        if (!$defaultShift) {
            $this->command->error('No active shift found. Please create a shift first.');
            return;
        }

        if (!$defaultOffice) {
            $this->command->error('No active office found. Please create an office first.');
            return;
        }

        // Update all employees without default schedule settings
        $employees = Employee::whereNull('default_shift_id')->get();

        foreach ($employees as $employee) {
            $employee->update([
                'default_shift_id' => $defaultShift->id,
                'default_office_id' => $defaultOffice->id,
                'default_work_type' => 'WFO',
            ]);

            $this->command->info("Updated employee: {$employee->user->full_name}");
        }

        $this->command->info("Updated {$employees->count()} employees with default schedule settings.");
    }
}
