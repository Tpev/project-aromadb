<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'profile_description' and 'profile_picture' columns to the 'users' table.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Adds a nullable text column for profile description
            $table->text('profile_description')->nullable()->after('about');

            // Adds a nullable string column for profile picture path
            $table->string('profile_picture')->nullable()->after('profile_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops 'profile_description' and 'profile_picture' columns from the 'users' table.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_description', 'profile_picture']);
        });
    }
}
