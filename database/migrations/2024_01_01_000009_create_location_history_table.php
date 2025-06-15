<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('location_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable(); // GPS accuracy in meters
            $table->string('action', 50); // check, clock_in, clock_out, etc
            $table->timestamp('timestamp');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('additional_data')->nullable(); // For storing extra location metadata
            $table->timestamps();

            $table->index(['user_id', 'timestamp']);
            $table->index(['latitude', 'longitude']);
            $table->index('action');
        });

        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 50); // office, restricted, custom
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius'); // in meters
            $table->json('polygon_coordinates')->nullable(); // For complex shapes
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('settings')->nullable(); // Additional geofence settings
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
            $table->index(['type', 'is_active']);
        });

        Schema::create('geofence_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('geofence_id')->nullable()->constrained()->onDelete('set null');
            $table->string('violation_type', 50); // outside_radius, suspicious_movement, etc
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('distance_from_center', 10, 2)->nullable();
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('is_resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'violation_type']);
            $table->index(['severity', 'is_resolved']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('geofence_violations');
        Schema::dropIfExists('geofences');
        Schema::dropIfExists('location_history');
    }
};
