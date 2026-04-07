<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_location_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('member');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['practice_location_id', 'user_id'], 'practice_location_members_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_location_members');
    }
};
