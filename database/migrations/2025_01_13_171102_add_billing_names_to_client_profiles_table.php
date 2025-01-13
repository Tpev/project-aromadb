<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingNamesToClientProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->string('first_name_billing')->nullable()->after('phone');
            $table->string('last_name_billing')->nullable()->after('first_name_billing');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn('first_name_billing');
            $table->dropColumn('last_name_billing');
        });
    }
}
