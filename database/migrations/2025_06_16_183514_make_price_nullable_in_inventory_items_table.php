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
        $table->decimal('price', 8, 2)->nullable()->change();
        $table->decimal('selling_price', 8, 2)->nullable()->change();
    });
}

public function down()
{
    Schema::table('inventory_items', function (Blueprint $table) {
        $table->decimal('price', 8, 2)->nullable(false)->change();
        $table->decimal('selling_price', 8, 2)->nullable(false)->change();
    });
}

};
