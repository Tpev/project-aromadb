<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('end_date_time')->nullable()->after('duration')->index();
        });

        DB::table('events')
            ->select(['id', 'start_date_time', 'duration'])
            ->orderBy('id')
            ->chunkById(200, function ($events): void {
                foreach ($events as $event) {
                    if ($event->start_date_time === null || (int) $event->duration < 1) {
                        continue;
                    }

                    DB::table('events')
                        ->where('id', $event->id)
                        ->update([
                            'end_date_time' => Carbon::parse($event->start_date_time)
                                ->addMinutes((int) $event->duration),
                        ]);
                }
            });

        Schema::table('unavailabilities', function (Blueprint $table) {
            $table->foreignId('event_id')
                ->nullable()
                ->after('user_id')
                ->unique()
                ->constrained('events')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('unavailabilities', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
        Schema::table('unavailabilities', function (Blueprint $table) {
            $table->dropUnique(['event_id']);
        });
        Schema::table('unavailabilities', function (Blueprint $table) {
            $table->dropColumn('event_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['end_date_time']);
        });
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('end_date_time');
        });
    }
};
