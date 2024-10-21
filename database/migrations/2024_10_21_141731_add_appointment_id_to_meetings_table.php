<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppointmentIdToMeetingsTable extends Migration
{
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']); // Drop the foreign key constraint
            $table->dropColumn('appointment_id'); // Remove the column
        });
    }
}
