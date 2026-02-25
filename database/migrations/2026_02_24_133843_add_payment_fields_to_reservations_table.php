<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('status')->default('confirmed'); // confirmed | pending_payment | paid | canceled
            $table->string('stripe_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->decimal('amount_ttc', 10, 2)->nullable();
            $table->string('currency', 10)->default('eur');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'stripe_session_id',
                'stripe_payment_intent_id',
                'amount_ttc',
                'currency',
            ]);
        });
    }
};