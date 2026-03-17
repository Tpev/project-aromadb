<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_voucher_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('EUR');

            $table->string('buyer_name')->nullable();
            $table->string('buyer_email');
            $table->string('buyer_phone')->nullable();

            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->string('status', 20)->default('pending'); // pending|paid|cancelled|failed
            $table->unsignedBigInteger('gift_voucher_id')->nullable();
            $table->unsignedBigInteger('sale_invoice_id')->nullable();

            $table->string('stripe_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->unique();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('gift_voucher_id')
                ->references('id')
                ->on('gift_vouchers')
                ->nullOnDelete();

            $table->foreign('sale_invoice_id')
                ->references('id')
                ->on('invoices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_voucher_orders');
    }
};

