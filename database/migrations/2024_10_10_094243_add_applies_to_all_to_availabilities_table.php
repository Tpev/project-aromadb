<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppliesToAllToAvailabilitiesTable extends Migration
{
    public function up()
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->boolean('applies_to_all')->default(false)->after('end_time');
        });
    }

    public function down()
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropColumn('applies_to_all');
        });
    }
}
