<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('client_profiles', function (Blueprint $table) {
        if (!Schema::hasColumn('client_profiles', 'password')) {
            $table->string('password')->nullable();
        }

        if (!Schema::hasColumn('client_profiles', 'email')) {
            $table->string('email')->nullable()->unique();
        }

        if (!Schema::hasColumn('client_profiles', 'remember_token')) {
            $table->rememberToken();
        }

        if (!Schema::hasColumn('client_profiles', 'email_verified_at')) {
            $table->timestamp('email_verified_at')->nullable();
        }
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