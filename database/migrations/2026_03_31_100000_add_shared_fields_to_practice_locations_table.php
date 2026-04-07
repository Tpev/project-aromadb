<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_locations', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('is_primary');
            $table->timestamp('shared_enabled_at')->nullable()->after('is_shared');
        });
    }

    public function down(): void
    {
        Schema::table('practice_locations', function (Blueprint $table) {
            $table->dropColumn(['is_shared', 'shared_enabled_at']);
        });
    }
};
