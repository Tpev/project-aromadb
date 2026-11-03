<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // No backfill: historical catalog prices may have changed.
            $table->unsignedInteger('gift_voucher_total_cents')->nullable();
        });
        Schema::table('gift_voucher_redemptions', function (Blueprint $table) {
            $table->index(['appointment_id', 'status'], 'gift_voucher_redemptions_appointment_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('gift_voucher_redemptions', fn (Blueprint $table) => $table->dropIndex('gift_voucher_redemptions_appointment_status_index'));
        Schema::table('appointments', fn (Blueprint $table) => $table->dropColumn('gift_voucher_total_cents'));
    }
};
