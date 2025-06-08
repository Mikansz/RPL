<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Permit Types Table
        Schema::create('permit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->boolean('affects_attendance')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Permits Table
        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('permit_type_id')->constrained()->onDelete('cascade');
            $table->date('request_date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('duration_hours', 5, 2)->nullable();
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Day Exchange Requests Table (Tukar Hari)
        Schema::create('day_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('original_work_date'); // Hari kerja yang ingin ditukar
            $table->date('replacement_date'); // Hari pengganti
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['original_work_date', 'replacement_date']);
        });

        // Overtime Requests Table
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('overtime_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('planned_hours', 5, 2);
            $table->decimal('actual_hours', 5, 2)->nullable();
            $table->text('work_description');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->decimal('overtime_rate', 8, 2)->nullable();
            $table->decimal('overtime_amount', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['overtime_date']);
        });

        // Leave Requests Table (Enhanced)
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->text('work_handover')->nullable(); // Serah terima pekerjaan
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->json('attachments')->nullable(); // Dokumen pendukung
            $table->boolean('is_half_day')->default(false);
            $table->enum('half_day_type', ['morning', 'afternoon'])->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['leave_type_id']);
        });

        // Permit Approvals Table (Multi-level approval)
        Schema::create('permit_approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable'); // permits, day_exchanges, overtime_requests, leave_requests
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->integer('approval_level')->default(1);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id']);
            $table->index(['approver_id', 'status']);
        });

        // Permit Settings Table
        Schema::create('permit_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('permit_settings');
        Schema::dropIfExists('permit_approvals');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('overtime_requests');
        Schema::dropIfExists('day_exchanges');
        Schema::dropIfExists('permits');
        Schema::dropIfExists('permit_types');
    }
};
