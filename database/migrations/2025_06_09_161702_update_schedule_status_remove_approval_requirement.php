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
        // Update existing 'scheduled' records to 'approved' and set approval fields
        DB::table('schedules')
            ->where('status', 'scheduled')
            ->update([
                'status' => 'approved',
                'approved_by' => DB::raw('created_by'),
                'approved_at' => DB::raw('created_at'),
                'updated_at' => now()
            ]);

        // Update the enum to remove 'scheduled' status and change default to 'approved'
        Schema::table('schedules', function (Blueprint $table) {
            $table->enum('status', ['approved', 'cancelled'])->default('approved')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original enum with 'scheduled' status and default
        Schema::table('schedules', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'approved', 'cancelled'])->default('scheduled')->change();
        });

        // Optionally, you could revert approved records back to scheduled
        // but this might not be desired in most cases
    }
};
