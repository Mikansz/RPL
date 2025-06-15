<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollPeriod;
use App\Models\User;
use Carbon\Carbon;

class PayrollPeriodSeeder extends Seeder
{
    public function run()
    {
        // Get admin user for created_by
        $adminUser = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$adminUser) {
            $this->command->error('Admin user not found. Please run UserSeeder first.');
            return;
        }

        $periods = [
            [
                'name' => 'Gaji Bulan ' . Carbon::now()->format('F Y'),
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->endOfMonth(),
                'pay_date' => Carbon::now()->endOfMonth()->addDays(5),
                'description' => 'Periode gaji bulan ' . Carbon::now()->format('F Y'),
                'status' => 'draft',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Gaji Bulan ' . Carbon::now()->subMonth()->format('F Y'),
                'start_date' => Carbon::now()->subMonth()->startOfMonth(),
                'end_date' => Carbon::now()->subMonth()->endOfMonth(),
                'pay_date' => Carbon::now()->subMonth()->endOfMonth()->addDays(5),
                'description' => 'Periode gaji bulan ' . Carbon::now()->subMonth()->format('F Y'),
                'status' => 'calculated',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Gaji Bulan ' . Carbon::now()->addMonth()->format('F Y'),
                'start_date' => Carbon::now()->addMonth()->startOfMonth(),
                'end_date' => Carbon::now()->addMonth()->endOfMonth(),
                'pay_date' => Carbon::now()->addMonth()->endOfMonth()->addDays(5),
                'description' => 'Periode gaji bulan ' . Carbon::now()->addMonth()->format('F Y'),
                'status' => 'draft',
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($periods as $periodData) {
            PayrollPeriod::firstOrCreate(
                [
                    'name' => $periodData['name'],
                    'start_date' => $periodData['start_date'],
                ],
                $periodData
            );
        }

        $this->command->info('✅ Payroll periods created successfully');
    }
}
