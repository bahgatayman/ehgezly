<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maincourt_amenities', function (Blueprint $table) {
            $table->foreignId('maincourt_id')->constrained('maincourts')->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained('amenities')->cascadeOnDelete();
            $table->primary(['maincourt_id', 'amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maincourt_amenities');
    }
};