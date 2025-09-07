<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('availabilities', function (Blueprint $t) {
            $t->foreignId('practice_location_id')
              ->nullable()
              ->after('user_id')
              ->constrained('practice_locations')
              ->nullOnDelete();

            $t->index(['user_id','day_of_week','practice_location_id']);
        });

        Schema::table('appointments', function (Blueprint $t) {
            $t->foreignId('practice_location_id')
              ->nullable()
              ->after('user_id')
              ->constrained('practice_locations')
              ->nullOnDelete();

            $t->index(['user_id','appointment_date','practice_location_id']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $t) {
            $t->dropConstrainedForeignId('practice_location_id');
            $t->dropIndex(['user_id','appointment_date','practice_location_id']);
        });

        Schema::table('availabilities', function (Blueprint $t) {
            $t->dropConstrainedForeignId('practice_location_id');
            $t->dropIndex(['user_id','day_of_week','practice_location_id']);
        });
    }
};
