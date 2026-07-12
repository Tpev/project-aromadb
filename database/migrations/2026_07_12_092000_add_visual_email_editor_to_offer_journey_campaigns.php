<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer_journey_message_campaigns', function (Blueprint $table) {
            $table->string('preheader', 255)->nullable()->after('subject');
            $table->json('content_json')->nullable()->after('body');
            $table->string('editor_version', 24)->nullable()->after('content_json');
            $table->json('style_json')->nullable()->after('editor_version');
        });

        Schema::create('offer_journey_email_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('offer_journey_message_campaign_id');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 80);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedInteger('size_bytes');
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'oj_email_assets_user_idx');
            $table->foreign('user_id', 'oj_email_assets_user_fk')
                ->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('offer_journey_message_campaign_id', 'oj_email_assets_campaign_fk')
                ->references('id')->on('offer_journey_message_campaigns')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_journey_email_assets');

        Schema::table('offer_journey_message_campaigns', function (Blueprint $table) {
            $table->dropColumn(['preheader', 'content_json', 'editor_version', 'style_json']);
        });
    }
};
