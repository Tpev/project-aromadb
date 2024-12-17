<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenToClientConseilTable extends Migration
{
    public function up()
    {
        Schema::table('client_conseil', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('client_conseil', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
}
