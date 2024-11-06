<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->unsignedBigInteger('appointment_id')->nullable()->after('invoice_number');

        $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->dropForeign(['appointment_id']);
        $table->dropColumn('appointment_id');
    });
}

};
