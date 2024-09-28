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
        $table->decimal('tax_rate', 5, 2)->default(0)->after('unit_price');
        $table->decimal('tax_amount', 10, 2)->default(0)->after('total_price');
        $table->decimal('total_price_with_tax', 10, 2)->default(0)->after('tax_amount');
    });
}

public function down()
{
    Schema::table('invoice_items', function (Blueprint $table) {
        $table->dropColumn(['tax_rate', 'tax_amount', 'total_price_with_tax']);
    });
}

};
