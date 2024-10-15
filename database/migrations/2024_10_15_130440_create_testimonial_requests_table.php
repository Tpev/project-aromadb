<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestimonialRequestsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('testimonial_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('therapist_id');
            $table->unsignedBigInteger('client_profile_id');
            $table->string('token')->unique();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('therapist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_profile_id')->references('id')->on('client_profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('testimonial_requests');
    }
}
