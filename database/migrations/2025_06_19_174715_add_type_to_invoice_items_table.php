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
        $table->string('type')->after('id')->nullable(); // ou notNullable si requis
    });
}

public function down()
{
    Schema::table('invoice_items', function (Blueprint $table) {
        $table->dropColumn('type');
    });
}

};
