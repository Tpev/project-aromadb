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
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('share_address_publicly')->default(false)->after('company_address');
        $table->boolean('share_phone_publicly')->default(false)->after('company_phone');
        $table->boolean('share_email_publicly')->default(false)->after('company_email');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['share_address_publicly', 'share_phone_publicly', 'share_email_publicly']);
    });
}

};
