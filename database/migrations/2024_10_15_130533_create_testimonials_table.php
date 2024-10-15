<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestimonialsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('testimonial_request_id')->unique();
            $table->unsignedBigInteger('therapist_id');
            $table->unsignedBigInteger('client_profile_id');
            $table->text('testimonial');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('testimonial_request_id')->references('id')->on('testimonial_requests')->onDelete('cascade');
            $table->foreign('therapist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_profile_id')->references('id')->on('client_profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('testimonials');
    }
}
