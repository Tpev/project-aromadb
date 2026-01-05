<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            // ⚠️ change() nécessite doctrine/dbal sur beaucoup d'installations Laravel/MySQL
            $table->string('invoice_number', 80)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->string('invoice_number', 80)->nullable(false)->change();
        });
    }
};
