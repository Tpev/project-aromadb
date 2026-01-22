<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_invites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('email')->index();
            $table->string('token', 64)->unique(); // token invitation
            $table->string('status', 24)->default('sent'); // sent|opened|signed_up|paid|expired

            $table->foreignId('invited_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('signed_up_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Plus tard : quand tu créditeras un mois manuellement
            $table->timestamp('reward_granted_at')->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->text('message')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['referrer_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_invites');
    }
};
