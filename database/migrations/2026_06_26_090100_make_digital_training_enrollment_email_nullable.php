<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('digital_training_enrollments')
            || ! Schema::hasColumn('digital_training_enrollments', 'participant_email')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE digital_training_enrollments MODIFY participant_email VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('digital_training_enrollments')
            || ! Schema::hasColumn('digital_training_enrollments', 'participant_email')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE digital_training_enrollments MODIFY participant_email VARCHAR(255) NOT NULL');
    }
};
