<?php

// database/migrations/2025_01_01_000000_add_onboarding_flags_to_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('skip_step3_onboarding')->default(false);
            $table->boolean('skip_step4_onboarding')->default(false);
            $table->boolean('referral_onboarding_completed')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'skip_step3_onboarding',
                'skip_step4_onboarding',
                'referral_onboarding_completed',
            ]);
        });
    }
};
