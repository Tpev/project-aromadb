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
Schema::table('inventory_items', function (Blueprint $table) {
    $table->decimal('vat_rate_purchase', 5, 2)->nullable()->after('price');
    $table->decimal('vat_rate_sale', 5, 2)->nullable()->after('selling_price');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            //
        });
    }
};
