<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('default_shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->foreignId('default_office_id')->nullable()->constrained('offices')->onDelete('set null');
            $table->enum('default_work_type', ['WFO', 'WFA'])->default('WFO');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['default_shift_id']);
            $table->dropForeign(['default_office_id']);
            $table->dropColumn(['default_shift_id', 'default_office_id', 'default_work_type']);
        });
    }
};
