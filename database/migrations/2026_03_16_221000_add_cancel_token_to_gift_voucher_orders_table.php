<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gift_voucher_orders')) {
            return;
        }

        if (! Schema::hasColumn('gift_voucher_orders', 'cancel_token')) {
            Schema::table('gift_voucher_orders', function (Blueprint $table) {
                $table->string('cancel_token', 64)->nullable()->after('currency');
            });
        }

        DB::table('gift_voucher_orders')
            ->whereNull('cancel_token')
            ->orderBy('id')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    DB::table('gift_voucher_orders')
                        ->where('id', $order->id)
                        ->update(['cancel_token' => Str::random(64)]);
                }
            });

        try {
            Schema::table('gift_voucher_orders', function (Blueprint $table) {
                $table->unique('cancel_token');
            });
        } catch (\Throwable $e) {
            // Index may already exist on some environments.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('gift_voucher_orders')) {
            return;
        }

        try {
            Schema::table('gift_voucher_orders', function (Blueprint $table) {
                $table->dropUnique('gift_voucher_orders_cancel_token_unique');
            });
        } catch (\Throwable $e) {
            // Ignore if index does not exist.
        }

        if (Schema::hasColumn('gift_voucher_orders', 'cancel_token')) {
            Schema::table('gift_voucher_orders', function (Blueprint $table) {
                $table->dropColumn('cancel_token');
            });
        }
    }
};
