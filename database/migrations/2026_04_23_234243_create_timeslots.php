<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeslots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');  // 08:00
            $table->time('end_time');    // 09:00
            $table->enum('status', [
                'available',
                'booked',
                'blocked',   // الـ owner أوقفه
            ])->default('available');
            $table->timestamps();

            // منيجيش نفس الـ slot مرتين لنفس الـ court
            $table->unique(['court_id', 'date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeslots');
    }
};