<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_view_logs', function (Blueprint $table) {
            $table->text('referrer')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('page_view_logs', function (Blueprint $table) {
            $table->string('referrer')->nullable()->change();
        });
    }
};
