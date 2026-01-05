<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {

            // 1) Drop old global unique index on record_number (name from your error)
            // If your index name differs, adjust accordingly.
            $table->dropUnique('receipts_record_number_unique');

            // 2) Add composite unique: per user
            $table->unique(['user_id', 'record_number'], 'receipts_user_record_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {

            // rollback: drop composite unique
            $table->dropUnique('receipts_user_record_number_unique');

            // restore old global unique
            $table->unique('record_number', 'receipts_record_number_unique');
        });
    }
};
