<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicenseTiersTable extends Migration
{
    public function up()
    {
        Schema::create('license_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Free', 'Standard', 'Premium'
            $table->integer('duration_days'); // License duration in days (e.g., 30 for a month)
            $table->boolean('is_trial')->default(false); // Indicates if this is a trial license
            $table->integer('trial_duration_days')->nullable(); // Duration of the trial in days (optional)
            $table->decimal('price', 8, 2); // Pricing for the license
            $table->json('features')->nullable(); // JSON to store license features (e.g., service limits, access types)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('license_tiers');
    }
}
