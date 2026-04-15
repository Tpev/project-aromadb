<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_training_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('digital_training_enrollments', 'viewed_block_ids')) {
                $table->json('viewed_block_ids')->nullable()->after('progress_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('digital_training_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('digital_training_enrollments', 'viewed_block_ids')) {
                $table->dropColumn('viewed_block_ids');
            }
        });
    }
};
