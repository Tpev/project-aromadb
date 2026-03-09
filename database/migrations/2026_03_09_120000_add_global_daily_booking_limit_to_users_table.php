<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Null = no global limit.
            $table->unsignedSmallInteger('global_daily_booking_limit')
                ->nullable()
                ->after('buffer_time_between_appointments');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('global_daily_booking_limit');
        });
    }
};
