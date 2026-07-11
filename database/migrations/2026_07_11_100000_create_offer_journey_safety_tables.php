<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer_journey_message_deliveries', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('sent_at');
            $table->timestamp('bounced_at')->nullable()->after('delivered_at');
            $table->timestamp('complained_at')->nullable()->after('bounced_at');
            $table->timestamp('rejected_at')->nullable()->after('complained_at');
            $table->index(['provider_message_id'], 'oj_messages_provider_id_idx');
        });

        Schema::table('offer_journey_consents', function (Blueprint $table) {
            $table->string('legal_basis', 40)->nullable()->after('purpose');
            $table->json('context_json')->nullable()->after('source');
        });

        Schema::create('offer_journey_deliverability_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_key', 100)->unique();
            $table->string('sns_message_id')->nullable()->index();
            $table->string('provider_message_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('offer_journey_message_delivery_id')->nullable();
            $table->string('event_type', 24);
            $table->string('event_subtype', 60)->nullable();
            $table->string('recipient_email')->nullable();
            $table->text('diagnostic')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'occurred_at'], 'oj_deliverability_type_date_idx');
            $table->index(['user_id', 'event_type', 'occurred_at'], 'oj_deliverability_user_type_idx');
            $table->foreign('offer_journey_message_delivery_id', 'oj_deliverability_delivery_fk')
                ->references('id')->on('offer_journey_message_deliveries')->nullOnDelete();
        });

        Schema::create('offer_journey_sender_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('marketing_paused')->default(false);
            $table->boolean('all_email_paused')->default(false);
            $table->text('pause_reason')->nullable();
            $table->unsignedBigInteger('paused_by_user_id')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamps();

            $table->foreign('paused_by_user_id', 'oj_sender_controls_actor_fk')
                ->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('offer_journey_support_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('action', 80);
            $table->string('target_type', 120)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('reason');
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->string('request_ip_hash', 64)->nullable();
            $table->string('request_id', 80)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['target_type', 'target_id'], 'oj_support_audit_target_idx');
            $table->index(['actor_user_id', 'occurred_at'], 'oj_support_audit_actor_idx');
            $table->foreign('actor_user_id', 'oj_support_audit_actor_fk')
                ->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_journey_support_audits');
        Schema::dropIfExists('offer_journey_sender_controls');
        Schema::dropIfExists('offer_journey_deliverability_events');

        Schema::table('offer_journey_consents', function (Blueprint $table) {
            $table->dropColumn(['legal_basis', 'context_json']);
        });

        Schema::table('offer_journey_message_deliveries', function (Blueprint $table) {
            $table->dropIndex('oj_messages_provider_id_idx');
            $table->dropColumn(['delivered_at', 'bounced_at', 'complained_at', 'rejected_at']);
        });
    }
};
