<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pack_purchases')) {
            return;
        }

        Schema::table('pack_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('pack_purchases', 'payment_mode')) {
                $table->string('payment_mode')->nullable(); // one_time|installments
            }

            if (!Schema::hasColumn('pack_purchases', 'payment_state')) {
                $table->string('payment_state')->nullable(); // pending|active|past_due|cancel_scheduled|completed|canceled|failed
            }

            if (!Schema::hasColumn('pack_purchases', 'installments_total')) {
                $table->unsignedTinyInteger('installments_total')->nullable();
            }

            if (!Schema::hasColumn('pack_purchases', 'installments_paid')) {
                $table->unsignedTinyInteger('installments_paid')->default(0);
            }

            if (!Schema::hasColumn('pack_purchases', 'installment_amount_cents')) {
                $table->unsignedInteger('installment_amount_cents')->nullable();
            }

            if (!Schema::hasColumn('pack_purchases', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')->nullable();
                $table->index('stripe_subscription_id', 'pack_purchases_stripe_subscription_id_index');
            }

            if (!Schema::hasColumn('pack_purchases', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable();
                $table->index('stripe_customer_id', 'pack_purchases_stripe_customer_id_index');
            }

            if (!Schema::hasColumn('pack_purchases', 'activated_at')) {
                $table->timestamp('activated_at')->nullable();
            }

            if (!Schema::hasColumn('pack_purchases', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }

            if (!Schema::hasColumn('pack_purchases', 'canceled_requested_at')) {
                $table->timestamp('canceled_requested_at')->nullable();
            }

            if (!Schema::hasColumn('pack_purchases', 'canceled_effective_at')) {
                $table->timestamp('canceled_effective_at')->nullable();
            }
        });

        try {
            Schema::table('pack_purchases', function (Blueprint $table) {
                $table->index(['payment_mode', 'payment_state'], 'pack_purchases_payment_mode_payment_state_index');
            });
        } catch (\Throwable $e) {
            // Ignore if index already exists.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('pack_purchases')) {
            return;
        }

        Schema::table('pack_purchases', function (Blueprint $table) {
            try {
                $table->dropIndex('pack_purchases_payment_mode_payment_state_index');
            } catch (\Throwable $e) {
                // Ignore if index does not exist.
            }

            if (Schema::hasColumn('pack_purchases', 'canceled_effective_at')) {
                $table->dropColumn('canceled_effective_at');
            }
            if (Schema::hasColumn('pack_purchases', 'canceled_requested_at')) {
                $table->dropColumn('canceled_requested_at');
            }
            if (Schema::hasColumn('pack_purchases', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            if (Schema::hasColumn('pack_purchases', 'activated_at')) {
                $table->dropColumn('activated_at');
            }
            if (Schema::hasColumn('pack_purchases', 'stripe_customer_id')) {
                try {
                    $table->dropIndex('pack_purchases_stripe_customer_id_index');
                } catch (\Throwable $e) {
                    // Ignore if index does not exist.
                }
                $table->dropColumn('stripe_customer_id');
            }
            if (Schema::hasColumn('pack_purchases', 'stripe_subscription_id')) {
                try {
                    $table->dropIndex('pack_purchases_stripe_subscription_id_index');
                } catch (\Throwable $e) {
                    // Ignore if index does not exist.
                }
                $table->dropColumn('stripe_subscription_id');
            }
            if (Schema::hasColumn('pack_purchases', 'installment_amount_cents')) {
                $table->dropColumn('installment_amount_cents');
            }
            if (Schema::hasColumn('pack_purchases', 'installments_paid')) {
                $table->dropColumn('installments_paid');
            }
            if (Schema::hasColumn('pack_purchases', 'installments_total')) {
                $table->dropColumn('installments_total');
            }
            if (Schema::hasColumn('pack_purchases', 'payment_state')) {
                $table->dropColumn('payment_state');
            }
            if (Schema::hasColumn('pack_purchases', 'payment_mode')) {
                $table->dropColumn('payment_mode');
            }
        });
    }
};
