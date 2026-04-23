<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maincourt_id')->constrained('maincourts')->cascadeOnDelete();
            $table->enum('day_of_week', [
                'saturday',
                'sunday',
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
            ]);
            $table->time('open_time');   // 06:00
            $table->time('close_time');  // 24:00
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            // منيجيش يتكرر نفس اليوم لنفس الملعب
            $table->unique(['maincourt_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};