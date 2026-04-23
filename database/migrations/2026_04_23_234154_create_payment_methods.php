<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maincourt_id')->constrained('maincourts')->cascadeOnDelete();
            $table->enum('type', [
                'instapay',
                'vodafone_cash',
                'etisalat_cash',
                'orange_cash',
                'we_pay',
            ]);
            $table->string('identifier'); // رقم التليفون أو الـ link
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};