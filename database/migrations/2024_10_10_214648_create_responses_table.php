<?php
// database/migrations/2024_10_10_000003_create_responses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->onDelete('cascade'); // Link to the questionnaire
            $table->foreignId('client_profile_id')->constrained()->onDelete('cascade'); // Link to the client profile
            $table->string('token')->unique(); // Unique token for filling out the questionnaire
            $table->json('answers'); // Store answers in JSON format
            $table->boolean('is_completed')->default(false); // Status of completion
            $table->timestamps(); // Timestamps for creation and updates
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('responses');
    }
}
