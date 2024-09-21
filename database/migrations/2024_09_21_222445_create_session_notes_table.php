<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_profile_id'); // Client profile the note belongs to
            $table->unsignedBigInteger('user_id'); // Therapist who created the note
            $table->text('note'); // Content of the session note
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
        Schema::dropIfExists('session_notes');
    }
}
