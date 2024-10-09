<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceNumberToInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add the invoice_number column after the id column
            $table->unsignedInteger('invoice_number')->nullable()->after('id');
            
            // Optional: Ensure that invoice_number is unique per user
            $table->unique(['user_id', 'invoice_number']);
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'invoice_number']);
            $table->dropColumn('invoice_number');
        });
    }
}
