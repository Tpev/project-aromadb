<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModifyUserAgentColumnInPageViewLogsTable extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'sqlite' || ! Schema::hasTable('page_view_logs')) {
            return;
        }

        DB::statement('ALTER TABLE page_view_logs MODIFY user_agent TEXT NULL');
    }

    public function down()
    {
        if (DB::getDriverName() === 'sqlite' || ! Schema::hasTable('page_view_logs')) {
            return;
        }

        DB::statement('ALTER TABLE page_view_logs MODIFY user_agent VARCHAR(255) NULL');
    }
}
