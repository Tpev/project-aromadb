<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Foreign key to users table
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('start_date_time');
            $table->integer('duration'); // in minutes
            $table->boolean('booking_required');
            $table->boolean('limited_spot');
            $table->integer('number_of_spot')->nullable();
            $table->unsignedBigInteger('associated_product')->nullable();
            $table->string('image')->nullable();
            $table->boolean('showOnPortail');
            $table->string('location');
            $table->timestamps();

            // Foreign key to products table (if applicable)
            $table->foreign('associated_product')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
