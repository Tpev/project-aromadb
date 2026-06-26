<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite' || ! Schema::hasTable('page_view_logs')) {
            return;
        }

        DB::statement('ALTER TABLE page_view_logs MODIFY referrer TEXT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite' || ! Schema::hasTable('page_view_logs')) {
            return;
        }

        DB::statement('ALTER TABLE page_view_logs MODIFY referrer VARCHAR(255) NULL');
    }
};
