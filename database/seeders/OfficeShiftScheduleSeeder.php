<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Office;
use App\Models\Shift;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;

class OfficeShiftScheduleSeeder extends Seeder
{
    public function run()
    {
        // Create Offices
        $offices = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Kantor Cabang Bandung',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'radius' => 150,
                'is_active' => true,
            ],
            [
                'name' => 'Kantor Cabang Surabaya',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'radius' => 120,
                'is_active' => true,
            ],
        ];

        foreach ($offices as $office) {
            Office::create($office);
        }

        // Create Shifts
        $shifts = [
            [
                'name' => 'Shift Pagi',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'is_active' => true,
            ],
            [
                'name' => 'Shift Siang',
                'start_time' => '13:00:00',
                'end_time' => '22:00:00',
                'is_active' => true,
            ],
            [
                'name' => 'Shift Malam',
                'start_time' => '22:00:00',
                'end_time' => '07:00:00',
                'is_active' => true,
            ],
            [
                'name' => 'Shift Fleksibel',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }

        // Create sample schedules for existing users
        $users = User::whereHas('employee')->take(5)->get();
        $officeIds = Office::pluck('id')->toArray();
        $shiftIds = Shift::pluck('id')->toArray();
        $workTypes = ['WFO', 'WFA'];

        foreach ($users as $user) {
            // Create schedules for the next 30 days
            for ($i = 0; $i < 30; $i++) {
                $scheduleDate = Carbon::today()->addDays($i);
                
                // Skip weekends for this example
                if ($scheduleDate->isWeekend()) {
                    continue;
                }

                $workType = $workTypes[array_rand($workTypes)];
                $officeId = $workType === 'WFO' ? $officeIds[array_rand($officeIds)] : null;
                $shiftId = $shiftIds[array_rand($shiftIds)];

                Schedule::create([
                    'user_id' => $user->id,
                    'shift_id' => $shiftId,
                    'office_id' => $officeId,
                    'schedule_date' => $scheduleDate,
                    'work_type' => $workType,
                    'status' => 'approved', // Auto approve for demo
                    'notes' => $workType === 'WFA' ? 'Remote work day' : 'Office work day',
                    'created_by' => 1, // Assuming admin user ID is 1
                    'approved_by' => 1,
                    'approved_at' => now(),
                ]);
            }
        }
    }
}
