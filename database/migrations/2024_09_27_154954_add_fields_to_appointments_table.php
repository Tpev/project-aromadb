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
    Schema::table('appointments', function (Blueprint $table) {
        $table->string('type')->nullable();
        $table->integer('duration')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropForeign(['product_id']);
        $table->dropColumn(['type', 'duration', 'product_id']);
    });
}

};
