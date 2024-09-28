<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToInvoicesTable extends Migration
{
    /**
     * Exécute les migrations.
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('invoice_date');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('total_amount');
            $table->text('notes')->nullable()->after('status');
        });
    }

    /**
     * Annule les migrations.
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'tax_amount', 'notes']);
        });
    }
}
