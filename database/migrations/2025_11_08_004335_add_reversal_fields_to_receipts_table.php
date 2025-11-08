<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_reversal_fields_to_receipts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('receipts', function (Blueprint $table) {
            $table->foreignId('reversal_of_id')->nullable()->constrained('receipts')->nullOnDelete();
            $table->boolean('is_reversal')->default(false);
            // Optional: prevent multiple reversals on the same source line
            $table->unique(['reversal_of_id','is_reversal']);
        });
    }
    public function down(): void {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropUnique(['reversal_of_id','is_reversal']);
            $table->dropConstrainedForeignId('reversal_of_id');
            $table->dropColumn('is_reversal');
        });
    }
};
