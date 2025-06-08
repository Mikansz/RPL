<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->integer('level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained();
            $table->foreignId('position_id')->constrained();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('hire_date');
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'internship', 'freelance']);
            $table->enum('employment_status', ['active', 'resigned', 'terminated', 'retired']);
            $table->decimal('basic_salary', 15, 2);
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account', 30)->nullable();
            $table->string('bank_account_name', 100)->nullable();
            $table->string('tax_id', 30)->nullable(); // NPWP
            $table->string('social_security_id', 30)->nullable(); // BPJS
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index(['department_id', 'employment_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
