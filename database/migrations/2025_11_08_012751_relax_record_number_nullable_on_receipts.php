<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            // allow NULL at insert time; model will backfill post-insert
            $table->unsignedBigInteger('record_number')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            // if you ever roll back, be careful: existing NULLs would break NOT NULL.
            // Only do this if you’re sure all rows are backfilled.
            $table->unsignedBigInteger('record_number')->nullable(false)->change();
        });
    }
};
