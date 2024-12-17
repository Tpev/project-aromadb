<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientConseilTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_conseil', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_profile_id');
            $table->unsignedBigInteger('conseil_id');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('client_profile_id')->references('id')->on('client_profiles')->onDelete('cascade');
            $table->foreign('conseil_id')->references('id')->on('conseils')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_conseil');
    }
}
