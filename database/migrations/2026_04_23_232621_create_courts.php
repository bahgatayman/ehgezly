<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maincourt_id')->constrained('maincourts')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', [
                'FIVE_A_SIDE',
                'SIX_A_SIDE',
                'SEVEN_A_SIDE',
                'ELEVEN_A_SIDE'
            ]);
            $table->enum('surface_type', [
                'grass',
                'artificial_turf',
                'cement'
            ])->default('artificial_turf');
            $table->decimal('price_per_hour', 10, 2);
            $table->enum('status', [
                'open',
                'closed',
                'maintenance'
            ])->default('open');
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};