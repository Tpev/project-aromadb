<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('digital_trainings')) {
            return;
        }

        Schema::table('digital_trainings', function (Blueprint $table) {
            if (! Schema::hasColumn('digital_trainings', 'free_access_is_open')) {
                $table->boolean('free_access_is_open')
                    ->default(false)
                    ->after('free_access_requires_identity');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('digital_trainings')) {
            return;
        }

        Schema::table('digital_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('digital_trainings', 'free_access_is_open')) {
                $table->dropColumn('free_access_is_open');
            }
        });
    }
};
