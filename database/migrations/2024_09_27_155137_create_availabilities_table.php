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
    Schema::create('availabilities', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->tinyInteger('day_of_week'); // 0 (Sunday) - 6 (Saturday)
        $table->time('start_time');
        $table->time('end_time');
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('availabilities');
}

};
