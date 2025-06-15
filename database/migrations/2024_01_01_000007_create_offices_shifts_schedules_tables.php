<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create offices table
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius')->default(100); // radius in meters
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active']);
        });

        // Create shifts table
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active']);
        });

        // Create schedules table
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->nullable()->constrained()->onDelete('set null');
            $table->date('schedule_date');
            $table->enum('work_type', ['WFO', 'WFA'])->default('WFO'); // Work From Office / Work From Anywhere
            $table->enum('status', ['scheduled', 'approved', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'schedule_date']);
            $table->index(['schedule_date', 'work_type']);
            $table->index(['shift_id', 'office_id']);
            $table->index(['status']);
        });

        // Add office_id to attendances table for tracking where attendance was recorded
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->constrained()->onDelete('set null');
            $table->index(['office_id']);
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
        });
        
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('offices');
    }
};
