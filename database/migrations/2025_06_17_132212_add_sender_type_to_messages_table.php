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
    Schema::table('messages', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->string('sender_type')->after('user_id')->default('client'); // or nullable()
    });
}

public function down()
{
    Schema::table('messages', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->dropColumn('sender_type');
    });
}

};
