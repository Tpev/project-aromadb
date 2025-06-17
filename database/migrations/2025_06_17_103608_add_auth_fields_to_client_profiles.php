<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
// database/migrations/xxxx_add_auth_fields_to_client_profiles.php
Schema::table('client_profiles', function (Blueprint $table) {
    $table->string('password')->nullable();
    $table->rememberToken();                 // adds remember_token
    $table->string('email')->nullable();
            
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            //
        });
    }
};