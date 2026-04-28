<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courtowners', function (Blueprint $table) {
            $table->decimal('commission_percentage', 5, 2)->default(5.00)->after('app_due_amount');
            $table->dropColumn('app_paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('courtowners', function (Blueprint $table) {
            $table->decimal('app_paid_amount', 10, 2)->default(0)->after('app_due_amount');
            $table->dropColumn('commission_percentage');
        });
    }
};
