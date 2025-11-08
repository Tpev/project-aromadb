<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            // simple column; we’ll fill it from the app right after insert
            $table->unsignedBigInteger('record_number')->nullable()->after('id');
        });

        // backfill existing rows
        DB::table('receipts')->update(['record_number' => DB::raw('id')]);

        // not null + unique + index
        Schema::table('receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('record_number')->nullable(false)->change();
            $table->unique('record_number', 'receipts_record_number_unique');
            $table->index('record_number', 'receipts_record_number_index');
        });

        // Optional: enforce at DB level when MySQL >= 8.0.16 (or MariaDB that honors CHECK)
        // If your engine doesn’t support CHECK, this statement will fail — we’ll swallow it.
        try {
            DB::statement("
                ALTER TABLE receipts
                ADD CONSTRAINT chk_receipts_record_number
                CHECK (record_number = id)
            ");
        } catch (\Throwable $e) {
            // CHECK not supported — app-level guard will still protect it.
        }
    }

    public function down(): void
    {
        // drop CHECK if it exists (ignore errors)
        try {
            DB::statement("ALTER TABLE receipts DROP CONSTRAINT chk_receipts_record_number");
        } catch (\Throwable $e) {}

        Schema::table('receipts', function (Blueprint $table) {
            $table->dropUnique('receipts_record_number_unique');
            $table->dropIndex('receipts_record_number_index');
            $table->dropColumn('record_number');
        });
    }
};
