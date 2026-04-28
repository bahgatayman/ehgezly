<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('timeslot_id')->constrained('timeslots')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('required_players');
            $table->unsignedInteger('current_players')->default(1);
            $table->enum('status', [
                'waiting_players',
                'ready_to_book',
                'booking_pending',
                'confirmed',
                'cancelled',
            ])->default('waiting_players');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_matches');
    }
};
