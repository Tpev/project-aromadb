<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_vouchers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id'); // therapist owner

            $table->string('code', 64)->unique()->index();

            $table->unsignedInteger('original_amount_cents');
            $table->unsignedInteger('remaining_amount_cents');

            $table->string('currency', 3)->default('EUR');

            $table->boolean('is_active')->default(true);

            $table->timestamp('expires_at')->nullable();

            $table->string('buyer_name')->nullable();
            $table->string('buyer_email')->nullable();

            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();

            $table->text('message')->nullable();

            $table->string('source')->default('manual'); // future-proof: stripe, etc.

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_vouchers');
    }
};
