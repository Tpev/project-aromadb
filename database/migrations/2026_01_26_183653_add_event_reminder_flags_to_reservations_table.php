<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'reminder_24h_sent_at')) {
                $table->timestamp('reminder_24h_sent_at')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('reservations', 'reminder_1h_sent_at')) {
                $table->timestamp('reminder_1h_sent_at')->nullable()->after('reminder_24h_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'reminder_24h_sent_at')) {
                $table->dropColumn('reminder_24h_sent_at');
            }
            if (Schema::hasColumn('reservations', 'reminder_1h_sent_at')) {
                $table->dropColumn('reminder_1h_sent_at');
            }
        });
    }
};
