<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Put it after is_therapist if that column exists; if not, remove ->after()
            $table->enum('onboarding_mode', ['self', 'assisted'])->nullable()->after('is_therapist');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onboarding_mode');
        });
    }
};
