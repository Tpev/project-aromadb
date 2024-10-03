<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLicensesTable extends Migration
{
    public function up()
    {
        Schema::create('user_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Link to the users table
            $table->foreignId('license_tier_id')->constrained('license_tiers'); // Link to the license tier
            $table->date('start_date'); // When the license starts
            $table->date('expiration_date'); // When the license expires
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_licenses');
    }
}
