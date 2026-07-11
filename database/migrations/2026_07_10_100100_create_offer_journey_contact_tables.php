<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_journey_pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('system_key', 40)->nullable();
            $table->string('color', 20)->default('gray');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'slug'], 'oj_pipeline_user_slug_unique');
        });

        Schema::create('offer_journey_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('pipeline_stage_id')->nullable();
            $table->string('email')->nullable();
            $table->string('email_normalized')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_normalized')->nullable();
            $table->string('contact_preference', 24)->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('status', 32)->default('new');
            $table->json('metadata')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'email_normalized'], 'oj_contacts_user_email_unique');
            $table->index(['user_id', 'status'], 'oj_contacts_user_status_idx');
            $table->foreign('pipeline_stage_id', 'oj_contacts_pipeline_fk')
                ->references('id')->on('offer_journey_pipeline_stages')->nullOnDelete();
        });

        Schema::create('offer_journey_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_id');
            $table->unsignedBigInteger('offer_journey_contact_id');
            $table->unsignedBigInteger('current_page_id')->nullable();
            $table->string('status', 32)->default('active');
            $table->string('first_utm_source')->nullable();
            $table->string('first_utm_medium')->nullable();
            $table->string('first_utm_campaign')->nullable();
            $table->string('last_utm_source')->nullable();
            $table->string('last_utm_medium')->nullable();
            $table->string('last_utm_campaign')->nullable();
            $table->timestamp('entered_at');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('exited_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['offer_journey_id', 'offer_journey_contact_id'],
                'oj_entries_journey_contact_unique'
            );
            $table->foreign('offer_journey_id', 'oj_entries_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
            $table->foreign('offer_journey_contact_id', 'oj_entries_contact_fk')
                ->references('id')->on('offer_journey_contacts')->cascadeOnDelete();
            $table->foreign('current_page_id', 'oj_entries_page_fk')
                ->references('id')->on('offer_journey_pages')->nullOnDelete();
        });

        Schema::create('offer_journey_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_contact_id');
            $table->unsignedBigInteger('offer_journey_id')->nullable();
            $table->string('purpose', 80);
            $table->string('status', 24)->default('granted');
            $table->string('text_version', 40);
            $table->text('text_snapshot');
            $table->string('source', 80)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_summary')->nullable();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            $table->index(['offer_journey_contact_id', 'purpose', 'status'], 'oj_consents_lookup_idx');
            $table->foreign('offer_journey_contact_id', 'oj_consents_contact_fk')
                ->references('id')->on('offer_journey_contacts')->cascadeOnDelete();
            $table->foreign('offer_journey_id', 'oj_consents_journey_fk')
                ->references('id')->on('offer_journeys')->nullOnDelete();
        });

        Schema::create('offer_journey_suppressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('offer_journey_contact_id')->nullable();
            $table->string('email_normalized');
            $table->string('type', 40)->default('unsubscribe');
            $table->string('reason')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('suppressed_at');
            $table->timestamps();

            $table->unique(['user_id', 'email_normalized', 'type'], 'oj_suppressions_unique');
            $table->foreign('offer_journey_contact_id', 'oj_suppressions_contact_fk')
                ->references('id')->on('offer_journey_contacts')->nullOnDelete();
        });

        Schema::create('offer_journey_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color', 20)->default('olive');
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'slug'], 'oj_tags_user_slug_unique');
        });

        Schema::create('offer_journey_contact_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_contact_id');
            $table->unsignedBigInteger('offer_journey_tag_id');
            $table->timestamps();

            $table->unique(
                ['offer_journey_contact_id', 'offer_journey_tag_id'],
                'oj_contact_tag_unique'
            );
            $table->foreign('offer_journey_contact_id', 'oj_contact_tag_contact_fk')
                ->references('id')->on('offer_journey_contacts')->cascadeOnDelete();
            $table->foreign('offer_journey_tag_id', 'oj_contact_tag_tag_fk')
                ->references('id')->on('offer_journey_tags')->cascadeOnDelete();
        });

        Schema::create('offer_journey_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('match_type', 16)->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('offer_journey_segment_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_segment_id');
            $table->string('field', 80);
            $table->string('operator', 32);
            $table->json('value_json')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('offer_journey_segment_id', 'oj_segment_rules_segment_fk')
                ->references('id')->on('offer_journey_segments')->cascadeOnDelete();
        });

        Schema::create('offer_journey_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('offer_journey_contact_id');
            $table->unsignedBigInteger('offer_journey_id')->nullable();
            $table->string('title');
            $table->text('note')->nullable();
            $table->string('priority', 16)->default('normal');
            $table->string('status', 24)->default('open');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'due_at'], 'oj_tasks_user_status_idx');
            $table->foreign('offer_journey_contact_id', 'oj_tasks_contact_fk')
                ->references('id')->on('offer_journey_contacts')->cascadeOnDelete();
            $table->foreign('offer_journey_id', 'oj_tasks_journey_fk')
                ->references('id')->on('offer_journeys')->nullOnDelete();
        });

        Schema::create('offer_journey_contact_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_contact_id');
            $table->unsignedBigInteger('offer_journey_id')->nullable();
            $table->string('type', 60);
            $table->string('title');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['offer_journey_contact_id', 'occurred_at'], 'oj_contact_activity_idx');
            $table->foreign('offer_journey_contact_id', 'oj_contact_activity_contact_fk')
                ->references('id')->on('offer_journey_contacts')->cascadeOnDelete();
            $table->foreign('offer_journey_id', 'oj_contact_activity_journey_fk')
                ->references('id')->on('offer_journeys')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_journey_contact_activities');
        Schema::dropIfExists('offer_journey_tasks');
        Schema::dropIfExists('offer_journey_segment_rules');
        Schema::dropIfExists('offer_journey_segments');
        Schema::dropIfExists('offer_journey_contact_tag');
        Schema::dropIfExists('offer_journey_tags');
        Schema::dropIfExists('offer_journey_suppressions');
        Schema::dropIfExists('offer_journey_consents');
        Schema::dropIfExists('offer_journey_entries');
        Schema::dropIfExists('offer_journey_contacts');
        Schema::dropIfExists('offer_journey_pipeline_stages');
    }
};
