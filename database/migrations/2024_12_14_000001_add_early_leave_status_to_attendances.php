<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add 'early_leave' to the status enum
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'early_leave', 'half_day', 'sick', 'leave', 'holiday') DEFAULT 'absent'");
    }

    public function down()
    {
        // Remove 'early_leave' from the status enum
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day', 'sick', 'leave', 'holiday') DEFAULT 'absent'");
    }
};
