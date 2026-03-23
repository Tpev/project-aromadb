<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('purchase_installments')) {
            return;
        }

        Schema::create('purchase_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_purchase_id')->constrained('pack_purchases')->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence_number');
            $table->unsignedInteger('amount_cents')->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('pending'); // pending|paid|failed
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('stripe_invoice_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->timestamps();

            $table->unique(['pack_purchase_id', 'sequence_number']);
            $table->index(['pack_purchase_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_installments');
    }
};

