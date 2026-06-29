<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_finance_customers', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_customer_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('invoice_prefix')->nullable();
            $table->string('default_payment_method_label')->nullable();
            $table->boolean('delinquent')->default(false)->index();
            $table->bigInteger('balance_cents')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('stripe_created_at')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_finance_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_subscription_id')->unique();
            $table->foreignId('stripe_finance_customer_id')->nullable()->constrained('stripe_finance_customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('status')->index();
            $table->string('collection_method')->nullable();
            $table->boolean('cancel_at_period_end')->default(false)->index();
            $table->timestamp('cancel_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable()->index();
            $table->timestamp('trial_start')->nullable();
            $table->timestamp('trial_end')->nullable()->index();
            $table->timestamp('next_payment_attempt')->nullable()->index();
            $table->bigInteger('amount_cents')->default(0);
            $table->string('currency', 10)->default('eur');
            $table->string('interval')->nullable()->index();
            $table->unsignedInteger('interval_count')->default(1);
            $table->string('product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string('price_id')->nullable();
            $table->string('price_nickname')->nullable();
            $table->string('license_label')->nullable();
            $table->string('coupon_id')->nullable();
            $table->string('coupon_name')->nullable();
            $table->string('promotion_code')->nullable();
            $table->decimal('discount_percent', 8, 2)->nullable();
            $table->bigInteger('discount_amount_cents')->nullable();
            $table->string('latest_invoice_id')->nullable()->index();
            $table->string('default_payment_method_label')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_finance_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_invoice_id')->unique();
            $table->foreignId('stripe_finance_customer_id')->nullable()->constrained('stripe_finance_customers')->nullOnDelete();
            $table->foreignId('stripe_finance_subscription_id')->nullable()->constrained('stripe_finance_subscriptions')->nullOnDelete();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('number')->nullable();
            $table->string('status')->nullable()->index();
            $table->string('billing_reason')->nullable();
            $table->string('collection_method')->nullable();
            $table->string('currency', 10)->default('eur');
            $table->bigInteger('subtotal_cents')->default(0);
            $table->bigInteger('total_cents')->default(0);
            $table->bigInteger('tax_cents')->default(0);
            $table->bigInteger('discount_cents')->default(0);
            $table->bigInteger('amount_due_cents')->default(0);
            $table->bigInteger('amount_paid_cents')->default(0);
            $table->bigInteger('amount_remaining_cents')->default(0);
            $table->boolean('attempted')->default(false);
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamp('next_payment_attempt')->nullable()->index();
            $table->timestamp('due_date')->nullable()->index();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamp('stripe_created_at')->nullable()->index();
            $table->string('hosted_invoice_url', 2048)->nullable();
            $table->string('invoice_pdf', 2048)->nullable();
            $table->string('last_payment_error_code')->nullable();
            $table->text('last_payment_error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_finance_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_balance_transaction_id');
            $table->string('stripe_source_id')->nullable()->index();
            $table->string('stripe_payout_id')->nullable()->index();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_invoice_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('type')->nullable()->index();
            $table->string('reporting_category')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->string('currency', 10)->default('eur');
            $table->bigInteger('amount_cents')->default(0);
            $table->bigInteger('fee_cents')->default(0);
            $table->bigInteger('net_cents')->default(0);
            $table->decimal('exchange_rate', 14, 6)->nullable();
            $table->timestamp('available_on')->nullable()->index();
            $table->timestamp('stripe_created_at')->nullable()->index();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique('stripe_balance_transaction_id', 'sfb_transactions_stripe_id_unique');
        });

        Schema::create('stripe_finance_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_payout_id')->unique();
            $table->string('balance_transaction_id')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->string('type')->nullable();
            $table->string('method')->nullable();
            $table->string('currency', 10)->default('eur');
            $table->bigInteger('amount_cents')->default(0);
            $table->timestamp('arrival_date')->nullable()->index();
            $table->timestamp('stripe_created_at')->nullable()->index();
            $table->boolean('automatic')->default(true);
            $table->string('description')->nullable();
            $table->string('statement_descriptor')->nullable();
            $table->string('reconciliation_status')->default('en_attente')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_finance_upcoming_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_subscription_id');
            $table->unsignedBigInteger('stripe_finance_customer_id')->nullable();
            $table->unsignedBigInteger('stripe_finance_subscription_id')->nullable();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('currency', 10)->default('eur');
            $table->bigInteger('subtotal_cents')->default(0);
            $table->bigInteger('total_cents')->default(0);
            $table->bigInteger('amount_due_cents')->default(0);
            $table->bigInteger('discount_cents')->default(0);
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamp('next_payment_attempt')->nullable()->index();
            $table->timestamp('due_date')->nullable()->index();
            $table->string('coupon_id')->nullable();
            $table->string('coupon_name')->nullable();
            $table->string('promotion_code')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('previewed_at')->nullable()->index();
            $table->timestamps();

            $table->unique('stripe_subscription_id', 'sfui_subscription_unique');
            $table->foreign('stripe_finance_customer_id', 'sfui_customer_fk')
                ->references('id')
                ->on('stripe_finance_customers')
                ->nullOnDelete();
            $table->foreign('stripe_finance_subscription_id', 'sfui_subscription_fk')
                ->references('id')
                ->on('stripe_finance_subscriptions')
                ->nullOnDelete();
        });

        Schema::create('stripe_finance_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stripe_finance_customer_id')->constrained('stripe_finance_customers')->cascadeOnDelete();
            $table->foreignId('stripe_finance_subscription_id')->nullable()->constrained('stripe_finance_subscriptions')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('note');
            $table->text('body');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_finance_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('status')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('records_synced')->default(0);
            $table->json('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_finance_sync_runs');
        Schema::dropIfExists('stripe_finance_notes');
        Schema::dropIfExists('stripe_finance_upcoming_invoices');
        Schema::dropIfExists('stripe_finance_payouts');
        Schema::dropIfExists('stripe_finance_balance_transactions');
        Schema::dropIfExists('stripe_finance_invoices');
        Schema::dropIfExists('stripe_finance_subscriptions');
        Schema::dropIfExists('stripe_finance_customers');
    }
};
