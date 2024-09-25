<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserAgentToPageViewLogsTable extends Migration
{
    public function up()
    {
        Schema::table('page_view_logs', function (Blueprint $table) {
            $table->string('user_agent')->nullable();  // User-Agent
        });
    }

    public function down()
    {
        Schema::table('page_view_logs', function (Blueprint $table) {
            $table->dropColumn(['user_agent']);
        });
    }
}
