<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('inventory_items', function (Blueprint $table) {
        $table->string('unit_type')->default('unit'); // 'unit', 'ml', 'drop'
        $table->decimal('quantity_per_unit', 8, 2)->nullable(); // e.g. 100ml or 150 drops
        $table->decimal('quantity_remaining', 8, 2)->nullable(); // actual current quantity
    });
}

public function down()
{
    Schema::table('inventory_items', function (Blueprint $table) {
        $table->dropColumn(['unit_type', 'quantity_per_unit', 'quantity_remaining']);
    });
}

};
