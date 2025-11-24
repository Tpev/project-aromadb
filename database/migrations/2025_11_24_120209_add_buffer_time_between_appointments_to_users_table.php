<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->integer('buffer_time_between_appointments')
              ->nullable()
              ->default(0)
              ->after('minimum_notice_hours'); // adapte si besoin
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('buffer_time_between_appointments');
    });
}

};
