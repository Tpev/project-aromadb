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
    Schema::table('invoices', function (Blueprint $table) {
        $table->decimal('total_tax_amount', 10, 2)->default(0)->after('total_amount');
        $table->decimal('total_amount_with_tax', 10, 2)->default(0)->after('total_tax_amount');
    });
}

public function down()
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->dropColumn(['total_tax_amount', 'total_amount_with_tax']);
    });
}

};
