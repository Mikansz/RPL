<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRule;

class AttendanceRuleSeeder extends Seeder
{
    public function run()
    {
        AttendanceRule::create([
            'name' => 'Jam Kerja Standar',
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
            'late_tolerance_minutes' => 15,
            'early_leave_tolerance_minutes' => 15,
            'overtime_multiplier' => 1.5,
            'is_default' => true,
            'is_active' => true,
        ]);

        AttendanceRule::create([
            'name' => 'Jam Kerja Shift Pagi',
            'work_start_time' => '06:00:00',
            'work_end_time' => '14:00:00',
            'break_start_time' => '10:00:00',
            'break_end_time' => '10:30:00',
            'late_tolerance_minutes' => 10,
            'early_leave_tolerance_minutes' => 10,
            'overtime_multiplier' => 1.5,
            'is_default' => false,
            'is_active' => true,
        ]);

        AttendanceRule::create([
            'name' => 'Jam Kerja Shift Sore',
            'work_start_time' => '14:00:00',
            'work_end_time' => '22:00:00',
            'break_start_time' => '18:00:00',
            'break_end_time' => '19:00:00',
            'late_tolerance_minutes' => 10,
            'early_leave_tolerance_minutes' => 10,
            'overtime_multiplier' => 2.0,
            'is_default' => false,
            'is_active' => true,
        ]);
    }
}
