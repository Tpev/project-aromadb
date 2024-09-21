<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_profile_id'); // Client profile associated with the appointment
            $table->unsignedBigInteger('user_id'); // Therapist who created the appointment
            $table->dateTime('appointment_date');
            $table->string('status')->default('scheduled'); // scheduled, canceled, completed, etc.
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key relations
            $table->foreign('client_profile_id')->references('id')->on('client_profiles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
