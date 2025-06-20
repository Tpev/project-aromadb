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
        $table->decimal('price_per_ml', 8, 2)->nullable()->after('price');
    });
}

public function down()
{
    Schema::table('inventory_items', function (Blueprint $table) {
        $table->dropColumn('price_per_ml');
    });
}

};
