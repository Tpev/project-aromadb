<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_journey_message_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('subject', 180);
            $table->text('body');
            $table->string('status', 24)->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedInteger('eligible_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->json('summary_json')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at'], 'oj_campaigns_due_idx');
            $table->index(['user_id', 'created_at'], 'oj_campaigns_user_idx');
        });

        Schema::create('offer_journey_message_campaign_journey', function (Blueprint $table) {
            $table->foreignId('offer_journey_message_campaign_id')->constrained('offer_journey_message_campaigns')->cascadeOnDelete();
            $table->foreignId('offer_journey_id')->constrained('offer_journeys')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['offer_journey_message_campaign_id', 'offer_journey_id'], 'oj_campaign_journey_pk');
        });

        Schema::create('offer_journey_abandonment_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_journey_id')->constrained('offer_journeys')->cascadeOnDelete();
            $table->unsignedBigInteger('offer_journey_contact_id');
            $table->unsignedBigInteger('offer_journey_entry_id')->nullable();
            $table->string('source_type', 80);
            $table->unsignedBigInteger('source_id');
            $table->string('state', 24)->default('started');
            $table->timestamp('started_at');
            $table->timestamp('reminder_due_at');
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('stop_reason')->nullable();
            $table->string('idempotency_key')->unique();
            $table->timestamps();

            $table->index(['state', 'reminder_due_at'], 'oj_abandonment_due_idx');
            $table->foreign('offer_journey_contact_id', 'oj_abandonment_contact_fk')->references('id')->on('offer_journey_contacts')->cascadeOnDelete();
            $table->foreign('offer_journey_entry_id', 'oj_abandonment_entry_fk')->references('id')->on('offer_journey_entries')->nullOnDelete();
        });

        Schema::create('offer_journey_saved_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('filters_json');
            $table->timestamps();
            $table->unique(['user_id', 'name'], 'oj_saved_filters_user_name_unique');
        });

        Schema::create('offer_journey_pipeline_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_journey_id')->nullable()->constrained('offer_journeys')->cascadeOnDelete();
            $table->string('period', 7);
            $table->unsignedInteger('target_count');
            $table->timestamps();
            $table->unique(['user_id', 'offer_journey_id', 'period'], 'oj_pipeline_goals_unique');
        });

        Schema::create('offer_journey_contact_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_filename');
            $table->string('status', 24)->default('preview');
            $table->string('consent_proof')->nullable();
            $table->json('rows_json');
            $table->json('report_json')->nullable();
            $table->json('created_contact_ids_json')->nullable();
            $table->timestamp('committed_at')->nullable();
            $table->timestamp('rolled_back_at')->nullable();
            $table->timestamps();
        });

        Schema::table('offer_journey_contacts', function (Blueprint $table) {
            $table->string('pipeline_outcome_reason')->nullable()->after('status');
            $table->timestamp('next_action_at')->nullable()->after('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::table('offer_journey_contacts', function (Blueprint $table) {
            $table->dropColumn(['pipeline_outcome_reason', 'next_action_at']);
        });
        Schema::dropIfExists('offer_journey_contact_imports');
        Schema::dropIfExists('offer_journey_pipeline_goals');
        Schema::dropIfExists('offer_journey_saved_filters');
        Schema::dropIfExists('offer_journey_abandonment_candidates');
        Schema::dropIfExists('offer_journey_message_campaign_journey');
        Schema::dropIfExists('offer_journey_message_campaigns');
    }
};
