<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('timeslot_id')->constrained('timeslots')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->decimal('total_price', 10, 2);
            $table->string('receipt_image_url');   // الـ screenshot
            $table->enum('status', [
                'pending',    // استنى موافقة الـ owner
                'confirmed',  // الـ owner وافق
                'rejected',   // الـ owner رفض
                'cancelled',  // اليوزر كنسل
                'completed',  // خلص
            ])->default('pending');
            $table->text('rejection_reason')->nullable(); // لو رفض ليه
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};