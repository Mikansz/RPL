<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run()
    {
        $leaveTypes = [
            [
                'name' => 'Cuti Tahunan',
                'code' => 'ANNUAL',
                'max_days_per_year' => 12,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Sakit',
                'code' => 'SICK',
                'max_days_per_year' => 30,
                'is_paid' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Melahirkan',
                'code' => 'MATERNITY',
                'max_days_per_year' => 90,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Menikah',
                'code' => 'MARRIAGE',
                'max_days_per_year' => 3,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Kematian Keluarga',
                'code' => 'BEREAVEMENT',
                'max_days_per_year' => 3,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Khitan/Baptis Anak',
                'code' => 'CHILD_CEREMONY',
                'max_days_per_year' => 2,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Haji/Umroh',
                'code' => 'PILGRIMAGE',
                'max_days_per_year' => 40,
                'is_paid' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Tanpa Gaji',
                'code' => 'UNPAID',
                'max_days_per_year' => 365,
                'is_paid' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::create($leaveType);
        }
    }
}
