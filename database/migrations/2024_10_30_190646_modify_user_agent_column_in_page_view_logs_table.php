<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUserAgentColumnInPageViewLogsTable extends Migration
{
    public function up()
    {
        Schema::table('page_view_logs', function (Blueprint $table) {
            // Change 'user_agent' from string to text
            $table->text('user_agent')->change();
        });
    }

    public function down()
    {
        Schema::table('page_view_logs', function (Blueprint $table) {
            // Revert 'user_agent' back to string with a length of 255
            $table->string('user_agent', 255)->change();
        });
    }
}
