<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove overtime type settings from permit_settings table
        DB::table('permit_settings')
            ->whereIn('key', ['overtime_rate_weekday', 'overtime_rate_weekend'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the overtime type settings if needed for rollback
        DB::table('permit_settings')->insert([
            [
                'key' => 'overtime_rate_weekday',
                'value' => '1.5',
                'type' => 'float',
                'description' => 'Overtime rate multiplier for weekdays',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'overtime_rate_weekend',
                'value' => '2.0',
                'type' => 'float',
                'description' => 'Overtime rate multiplier for weekends',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};
