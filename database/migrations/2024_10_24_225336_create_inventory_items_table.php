<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryItemsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key to users table
            $table->string('name');
            $table->string('reference')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Purchase price
            $table->decimal('selling_price', 10, 2);
            $table->integer('quantity_in_stock');
            $table->string('brand')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
}
