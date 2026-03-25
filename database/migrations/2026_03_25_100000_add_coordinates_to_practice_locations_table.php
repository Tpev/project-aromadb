<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('practice_locations')) {
            return;
        }

        Schema::table('practice_locations', function (Blueprint $table) {
            if (! Schema::hasColumn('practice_locations', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('country');
            }

            if (! Schema::hasColumn('practice_locations', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('practice_locations')) {
            return;
        }

        Schema::table('practice_locations', function (Blueprint $table) {
            $drops = [];

            if (Schema::hasColumn('practice_locations', 'latitude')) {
                $drops[] = 'latitude';
            }

            if (Schema::hasColumn('practice_locations', 'longitude')) {
                $drops[] = 'longitude';
            }

            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
