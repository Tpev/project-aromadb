<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_location_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_location_id')->constrained()->cascadeOnDelete();
            $table->string('invited_email');
            $table->foreignId('invited_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('invited_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 100)->unique();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamps();

            $table->index(['practice_location_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_location_invites');
    }
};
