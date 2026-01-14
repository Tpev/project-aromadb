<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_voucher_redemptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('gift_voucher_id');
            $table->unsignedBigInteger('user_id'); // therapist who redeemed

            $table->unsignedInteger('amount_cents');

            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();

            $table->string('note', 255)->nullable();

            $table->timestamps();

            $table->foreign('gift_voucher_id')
                ->references('id')->on('gift_vouchers')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->index(['gift_voucher_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_voucher_redemptions');
    }
};
