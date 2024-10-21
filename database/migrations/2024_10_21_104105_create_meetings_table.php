<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingsTable extends Migration
{
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('start_time');
            $table->integer('duration'); // Duration in minutes
            $table->string('participant_email')->nullable(); // For email address
            $table->foreignId('client_profile_id')->nullable()->constrained('client_profiles'); // If using client profiles
            $table->string('room_token')->unique(); // Secure token for the room
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meetings');
    }
}
