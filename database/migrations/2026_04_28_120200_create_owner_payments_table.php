<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('courtowners')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_type', [
                'instapay',
                'vodafone_cash',
                'etisalat_cash',
                'orange_cash',
                'we_pay',
            ]);
            $table->string('receipt_image_url', 255);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_payments');
    }
};
