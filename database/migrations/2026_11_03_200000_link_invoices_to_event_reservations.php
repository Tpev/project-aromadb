<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('event_reservation_id')->nullable()->unique()
                ->constrained('reservations')->nullOnDelete();
            $table->json('event_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['event_reservation_id']);
            $table->dropUnique(['event_reservation_id']);
            $table->dropColumn(['event_reservation_id', 'event_snapshot']);
        });
    }
};
