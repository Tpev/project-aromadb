<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_profile_offer_journey_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('client_profile_id');
            $table->unsignedBigInteger('offer_journey_tag_id');
            $table->timestamps();

            $table->primary(['client_profile_id', 'offer_journey_tag_id'], 'cp_oj_tag_pk');
            $table->foreign('client_profile_id', 'cp_oj_tag_client_fk')
                ->references('id')->on('client_profiles')->cascadeOnDelete();
            $table->foreign('offer_journey_tag_id', 'cp_oj_tag_tag_fk')
                ->references('id')->on('offer_journey_tags')->cascadeOnDelete();
        });

        Schema::table('offer_journey_message_campaigns', function (Blueprint $table) {
            $table->string('audience_type', 24)->default('journeys')->after('created_by_user_id');
            $table->unsignedBigInteger('offer_journey_segment_id')->nullable()->after('audience_type');
            $table->index(['user_id', 'audience_type'], 'oj_campaigns_audience_idx');
            $table->foreign('offer_journey_segment_id', 'oj_campaigns_segment_fk')
                ->references('id')->on('offer_journey_segments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        Schema::table('offer_journey_message_campaigns', function (Blueprint $table) use ($isSqlite) {
            $table->dropForeign($isSqlite ? ['offer_journey_segment_id'] : 'oj_campaigns_segment_fk');
            $table->dropIndex('oj_campaigns_audience_idx');
            $table->dropColumn(['audience_type', 'offer_journey_segment_id']);
        });

        Schema::dropIfExists('client_profile_offer_journey_tag');
    }
};
