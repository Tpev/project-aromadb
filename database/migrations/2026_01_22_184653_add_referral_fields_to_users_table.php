<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('referred_by_user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('referral_invite_id')
                ->nullable()
                ->after('referred_by_user_id')
                ->constrained('referral_invites')
                ->nullOnDelete();

            $table->string('referral_code_used', 64)->nullable()->after('referral_invite_id');

            $table->timestamp('referral_attributed_at')->nullable()->after('referral_code_used');
            $table->timestamp('referral_converted_at')->nullable()->after('referral_attributed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_user_id');
            $table->dropConstrainedForeignId('referral_invite_id');
            $table->dropColumn([
                'referral_code_used',
                'referral_attributed_at',
                'referral_converted_at',
            ]);
        });
    }
};
