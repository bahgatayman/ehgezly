<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE timeslots MODIFY status ENUM('available','booked','blocked','pending_match') NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE timeslots MODIFY status ENUM('available','booked','blocked') NOT NULL DEFAULT 'available'");
    }
};
