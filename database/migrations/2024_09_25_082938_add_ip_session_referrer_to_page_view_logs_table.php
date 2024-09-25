<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpSessionReferrerToPageViewLogsTable extends Migration
{
    public function up()
    {
        Schema::table('page_view_logs', function (Blueprint $table) {
            $table->string('ip_address')->nullable();  // IP address
            $table->string('session_id')->nullable();  // Session ID
            $table->string('referrer')->nullable();    // Referrer URL
        });
    }

    public function down()
    {
        Schema::table('page_view_logs', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'session_id', 'referrer']);
        });
    }
}
