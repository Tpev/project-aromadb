<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicenseHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('license_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Link to the users table
            $table->foreignId('license_tier_id')->constrained('license_tiers'); // Link to the license tier
            $table->date('start_date'); // Start date of the license
            $table->date('end_date')->nullable(); // End date, nullable if still active
            $table->string('status')->default('active'); // License status (e.g., 'active', 'expired', 'canceled')
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('license_histories');
    }
}
