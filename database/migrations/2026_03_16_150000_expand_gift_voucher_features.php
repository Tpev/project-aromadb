<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'gift_voucher_online_enabled')) {
                $table->boolean('gift_voucher_online_enabled')
                    ->default(false)
                    ->after('accept_online_appointments');
            }

            if (! Schema::hasColumn('users', 'gift_voucher_background_mode')) {
                $table->string('gift_voucher_background_mode', 20)
                    ->default('default')
                    ->after('gift_voucher_online_enabled');
            }

            if (! Schema::hasColumn('users', 'gift_voucher_background_path')) {
                $table->string('gift_voucher_background_path')
                    ->nullable()
                    ->after('gift_voucher_background_mode');
            }

            if (! Schema::hasColumn('users', 'gift_voucher_background_updated_at')) {
                $table->timestamp('gift_voucher_background_updated_at')
                    ->nullable()
                    ->after('gift_voucher_background_path');
            }
        });

        Schema::table('gift_vouchers', function (Blueprint $table) {
            if (! Schema::hasColumn('gift_vouchers', 'buyer_phone')) {
                $table->string('buyer_phone', 40)->nullable()->after('buyer_email');
            }

            if (! Schema::hasColumn('gift_vouchers', 'sale_channel')) {
                $table->string('sale_channel', 30)->nullable()->after('source');
            }

            if (! Schema::hasColumn('gift_vouchers', 'sale_status')) {
                $table->string('sale_status', 20)->default('paid')->after('sale_channel');
            }

            if (! Schema::hasColumn('gift_vouchers', 'sale_invoice_id')) {
                $table->unsignedBigInteger('sale_invoice_id')->nullable()->after('sale_status');
                $table->index('sale_invoice_id');
            }

            if (! Schema::hasColumn('gift_vouchers', 'background_mode_snapshot')) {
                $table->string('background_mode_snapshot', 20)->nullable()->after('sale_invoice_id');
            }

            if (! Schema::hasColumn('gift_vouchers', 'background_path_snapshot')) {
                $table->string('background_path_snapshot')->nullable()->after('background_mode_snapshot');
            }
        });

        Schema::table('gift_voucher_redemptions', function (Blueprint $table) {
            if (! Schema::hasColumn('gift_voucher_redemptions', 'status')) {
                $table->string('status', 20)->default('applied')->after('note');
            }

            if (! Schema::hasColumn('gift_voucher_redemptions', 'source')) {
                $table->string('source', 30)->nullable()->after('status');
            }

            if (! Schema::hasColumn('gift_voucher_redemptions', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('source');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'gift_voucher_id')) {
                $table->unsignedBigInteger('gift_voucher_id')->nullable()->after('stripe_session_id');
                $table->index('gift_voucher_id');
            }

            if (! Schema::hasColumn('appointments', 'gift_voucher_amount_cents')) {
                $table->unsignedInteger('gift_voucher_amount_cents')->nullable()->after('gift_voucher_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'gift_voucher_amount_cents')) {
                $table->dropColumn('gift_voucher_amount_cents');
            }
            if (Schema::hasColumn('appointments', 'gift_voucher_id')) {
                $table->dropIndex(['gift_voucher_id']);
                $table->dropColumn('gift_voucher_id');
            }
        });

        Schema::table('gift_voucher_redemptions', function (Blueprint $table) {
            if (Schema::hasColumn('gift_voucher_redemptions', 'released_at')) {
                $table->dropColumn('released_at');
            }
            if (Schema::hasColumn('gift_voucher_redemptions', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('gift_voucher_redemptions', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('gift_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('gift_vouchers', 'background_path_snapshot')) {
                $table->dropColumn('background_path_snapshot');
            }
            if (Schema::hasColumn('gift_vouchers', 'background_mode_snapshot')) {
                $table->dropColumn('background_mode_snapshot');
            }
            if (Schema::hasColumn('gift_vouchers', 'sale_invoice_id')) {
                $table->dropIndex(['sale_invoice_id']);
                $table->dropColumn('sale_invoice_id');
            }
            if (Schema::hasColumn('gift_vouchers', 'sale_status')) {
                $table->dropColumn('sale_status');
            }
            if (Schema::hasColumn('gift_vouchers', 'sale_channel')) {
                $table->dropColumn('sale_channel');
            }
            if (Schema::hasColumn('gift_vouchers', 'buyer_phone')) {
                $table->dropColumn('buyer_phone');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'gift_voucher_background_updated_at')) {
                $table->dropColumn('gift_voucher_background_updated_at');
            }
            if (Schema::hasColumn('users', 'gift_voucher_background_path')) {
                $table->dropColumn('gift_voucher_background_path');
            }
            if (Schema::hasColumn('users', 'gift_voucher_background_mode')) {
                $table->dropColumn('gift_voucher_background_mode');
            }
            if (Schema::hasColumn('users', 'gift_voucher_online_enabled')) {
                $table->dropColumn('gift_voucher_online_enabled');
            }
        });
    }
};

