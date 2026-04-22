<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('community_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('channel_type', 20)->default('discussion');
            $table->string('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('community_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_profile_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('invited');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['community_group_id', 'client_profile_id'], 'community_members_unique');
        });

        Schema::create('community_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sender_type', 20);
            $table->text('content');
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();

            $table->index(['community_channel_id', 'created_at'], 'community_messages_channel_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_messages');
        Schema::dropIfExists('community_members');
        Schema::dropIfExists('community_channels');
        Schema::dropIfExists('community_groups');
    }
};
