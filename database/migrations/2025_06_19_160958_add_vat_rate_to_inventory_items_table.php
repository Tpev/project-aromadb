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
        $table->decimal('vat_rate', 5, 2)->nullable()->after('selling_price');
    });
}

public function down()
{
    Schema::table('inventory_items', function (Blueprint $table) {
        $table->dropColumn('vat_rate');
    });
}

};
