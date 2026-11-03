<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('gift_voucher_validity_months')->nullable();
        });
        Schema::table('gift_voucher_orders', function (Blueprint $table) {
            $table->unsignedSmallInteger('validity_months')->nullable();
            $table->dateTime('purchased_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gift_voucher_orders', fn (Blueprint $table) => $table->dropColumn(['validity_months', 'purchased_at']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('gift_voucher_validity_months'));
    }
};
