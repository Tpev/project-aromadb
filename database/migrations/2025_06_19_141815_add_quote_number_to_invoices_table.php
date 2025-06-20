<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->string('quote_number')->nullable()->after('invoice_number');
    });
}

public function down(): void
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->dropColumn('quote_number');
    });
}

};
