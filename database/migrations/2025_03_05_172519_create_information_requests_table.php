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
    Schema::create('information_requests', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('therapist_id');
        $table->string('first_name');
        $table->string('last_name');
        $table->string('email');
        $table->string('phone')->nullable();
        $table->text('message');
        $table->timestamps();

        $table->foreign('therapist_id')->references('id')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('information_requests');
    }
};
