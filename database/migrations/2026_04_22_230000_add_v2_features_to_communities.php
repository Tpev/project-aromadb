<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_channels', function (Blueprint $table) {
            if (!Schema::hasColumn('community_channels', 'pinned_community_message_id')) {
                $table->unsignedBigInteger('pinned_community_message_id')->nullable()->after('is_active');
                $table->foreign('pinned_community_message_id', 'cc_pinned_msg_fk')
                    ->references('id')
                    ->on('community_messages')
                    ->nullOnDelete();
            }
        });

        Schema::table('community_members', function (Blueprint $table) {
            if (!Schema::hasColumn('community_members', 'invitation_email_sent_at')) {
                $table->timestamp('invitation_email_sent_at')->nullable()->after('invited_at');
            }

            if (!Schema::hasColumn('community_members', 'removed_at')) {
                $table->timestamp('removed_at')->nullable()->after('joined_at');
            }
        });

        if (!Schema::hasTable('community_message_attachments')) {
            Schema::create('community_message_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('community_message_id');
                $table->string('file_path');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->timestamps();

                $table->foreign('community_message_id', 'cma_message_fk')
                    ->references('id')
                    ->on('community_messages')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('community_message_attachments')) {
            Schema::dropIfExists('community_message_attachments');
        }

        Schema::table('community_members', function (Blueprint $table) {
            if (Schema::hasColumn('community_members', 'removed_at')) {
                $table->dropColumn('removed_at');
            }

            if (Schema::hasColumn('community_members', 'invitation_email_sent_at')) {
                $table->dropColumn('invitation_email_sent_at');
            }
        });

        Schema::table('community_channels', function (Blueprint $table) {
            if (Schema::hasColumn('community_channels', 'pinned_community_message_id')) {
                $table->dropForeign('cc_pinned_msg_fk');
                $table->dropColumn('pinned_community_message_id');
            }
        });
    }
};
