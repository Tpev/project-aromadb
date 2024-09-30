<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyDetailsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new fields for company details and public sharing preferences
            $table->text('about')->nullable(); // About us section
            $table->json('services')->nullable(); // Services offered, stored as a JSON array

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the added fields if the migration is rolled back
            $table->dropColumn('about');
            $table->dropColumn('services');
        });
    }


};
