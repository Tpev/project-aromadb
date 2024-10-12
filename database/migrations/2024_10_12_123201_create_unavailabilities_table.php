<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnavailabilitiesTable extends Migration
{
    public function up()
    {
        Schema::create('unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Reference to the user
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('reason')->nullable(); // Optional reason field
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unavailabilities');
    }
}
