<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyUserAgentColumnInPageViewLogsTable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE page_view_logs MODIFY user_agent TEXT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE page_view_logs MODIFY user_agent VARCHAR(255) NULL');
    }
}
