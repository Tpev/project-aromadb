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
            if (! Schema::hasColumn('digital_trainings', 'free_access_requires_identity')) {
                $table->boolean('free_access_requires_identity')
                    ->default(false)
                    ->after('is_free');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('digital_trainings')) {
            return;
        }

        Schema::table('digital_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('digital_trainings', 'free_access_requires_identity')) {
                $table->dropColumn('free_access_requires_identity');
            }
        });
    }
};
