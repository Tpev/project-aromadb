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
    Schema::table('messages', function (Blueprint $table) {
        $table->foreignId('user_id')->nullable()->after('client_profile_id')->constrained()->onDelete('set null');
    });
}

public function down()
{
    Schema::table('messages', function (Blueprint $table) {
        $table->dropConstrainedForeignId('user_id');
    });
}

};
