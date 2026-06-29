<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_finance_products', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_product_id')->unique();
            $table->string('name')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('stripe_created_at')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_finance_prices', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_price_id')->unique();
            $table->string('stripe_product_id')->nullable()->index();
            $table->string('nickname')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->string('currency', 10)->default('eur');
            $table->bigInteger('unit_amount_cents')->default(0);
            $table->string('billing_scheme')->nullable();
            $table->string('type')->nullable();
            $table->string('interval')->nullable()->index();
            $table->unsignedInteger('interval_count')->default(1);
            $table->string('lookup_key')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('stripe_created_at')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_finance_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_coupon_id')->unique();
            $table->string('name')->nullable();
            $table->boolean('valid')->default(true)->index();
            $table->string('duration')->nullable();
            $table->unsignedInteger('duration_in_months')->nullable();
            $table->decimal('percent_off', 8, 2)->nullable();
            $table->bigInteger('amount_off_cents')->nullable();
            $table->string('currency', 10)->nullable();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('times_redeemed')->default(0);
            $table->timestamp('redeem_by')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('stripe_created_at')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_finance_promotion_codes', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_promotion_code_id')->unique();
            $table->string('code')->nullable()->index();
            $table->string('stripe_coupon_id')->nullable()->index();
            $table->boolean('active')->default(true)->index();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('times_redeemed')->default(0);
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('stripe_created_at')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_finance_payments', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_charge_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_invoice_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('stripe_balance_transaction_id')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->boolean('paid')->default(false)->index();
            $table->boolean('captured')->default(false);
            $table->boolean('refunded')->default(false);
            $table->boolean('disputed')->default(false)->index();
            $table->string('currency', 10)->default('eur');
            $table->bigInteger('amount_cents')->default(0);
            $table->bigInteger('amount_captured_cents')->default(0);
            $table->bigInteger('amount_refunded_cents')->default(0);
            $table->bigInteger('fee_cents')->default(0);
            $table->bigInteger('net_cents')->default(0);
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->string('payment_method_type')->nullable();
            $table->string('payment_method_label')->nullable();
            $table->string('receipt_url', 2048)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('stripe_created_at')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_finance_payments');
        Schema::dropIfExists('stripe_finance_promotion_codes');
        Schema::dropIfExists('stripe_finance_coupons');
        Schema::dropIfExists('stripe_finance_prices');
        Schema::dropIfExists('stripe_finance_products');
    }
};
