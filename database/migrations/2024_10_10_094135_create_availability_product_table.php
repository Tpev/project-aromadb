<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailabilityProductTable extends Migration
{
    public function up()
    {
        Schema::create('availability_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['availability_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('availability_product');
    }
}
