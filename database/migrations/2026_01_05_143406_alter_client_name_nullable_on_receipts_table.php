<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            // change() nécessite souvent doctrine/dbal
            $table->string('client_name', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->string('client_name', 255)->nullable(false)->change();
        });
    }
};
