<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 0 = cancellation allowed anytime (safe default)
            $table->unsignedSmallInteger('cancellation_notice_hours')
                ->default(0)
                ->after('minimum_notice_hours');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cancellation_notice_hours');
        });
    }
};
