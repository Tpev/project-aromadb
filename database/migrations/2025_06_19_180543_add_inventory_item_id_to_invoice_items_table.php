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
    Schema::table('invoice_items', function (Blueprint $table) {
        $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('invoice_items', function (Blueprint $table) {
        $table->dropForeign(['inventory_item_id']);
        $table->dropColumn('inventory_item_id');
    });
}

};
