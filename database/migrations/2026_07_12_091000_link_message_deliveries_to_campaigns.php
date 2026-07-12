<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer_journey_message_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('offer_journey_message_campaign_id')->nullable()->after('offer_journey_contact_id');
            $table->index('offer_journey_message_campaign_id', 'oj_messages_campaign_idx');
            $table->foreign('offer_journey_message_campaign_id', 'oj_messages_campaign_fk')
                ->references('id')->on('offer_journey_message_campaigns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        Schema::table('offer_journey_message_deliveries', function (Blueprint $table) use ($isSqlite) {
            $table->dropForeign($isSqlite ? ['offer_journey_message_campaign_id'] : 'oj_messages_campaign_fk');
            $table->dropIndex('oj_messages_campaign_idx');
            $table->dropColumn('offer_journey_message_campaign_id');
        });
    }
};
