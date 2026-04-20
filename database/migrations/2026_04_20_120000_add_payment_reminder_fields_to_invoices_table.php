<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'last_payment_reminder_sent_at')) {
                $table->dateTime('last_payment_reminder_sent_at')->nullable()->after('sent_at');
            }

            if (!Schema::hasColumn('invoices', 'payment_reminder_count')) {
                $table->unsignedInteger('payment_reminder_count')->default(0)->after('last_payment_reminder_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'payment_reminder_count')) {
                $table->dropColumn('payment_reminder_count');
            }

            if (Schema::hasColumn('invoices', 'last_payment_reminder_sent_at')) {
                $table->dropColumn('last_payment_reminder_sent_at');
            }
        });
    }
};
