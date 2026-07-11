<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_journey_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('offer_journey_id');
            $table->string('name');
            $table->string('status', 24)->default('draft');
            $table->string('trigger_type', 60)->default('lead_captured');
            $table->string('reentry_mode', 24)->default('once');
            $table->unsignedInteger('reentry_delay_days')->nullable();
            $table->string('quiet_hours_start', 5)->nullable();
            $table->string('quiet_hours_end', 5)->nullable();
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamps();

            $table->index(['offer_journey_id', 'status'], 'oj_automations_journey_status_idx');
            $table->foreign('offer_journey_id', 'oj_automations_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
        });

        Schema::create('offer_journey_automation_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_automation_id');
            $table->unsignedInteger('version_number');
            $table->string('status', 24)->default('draft');
            $table->json('definition_json')->nullable();
            $table->unsignedBigInteger('published_by_user_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['offer_journey_automation_id', 'version_number'],
                'oj_auto_versions_number_unique'
            );
            $table->foreign('offer_journey_automation_id', 'oj_auto_versions_automation_fk')
                ->references('id')->on('offer_journey_automations')->cascadeOnDelete();
            $table->foreign('published_by_user_id', 'oj_auto_versions_publisher_fk')
                ->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('offer_journey_automation_nodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_automation_version_id');
            $table->string('node_key', 50);
            $table->string('type', 24);
            $table->string('name');
            $table->json('config_json')->nullable();
            $table->string('next_node_key', 50)->nullable();
            $table->string('yes_node_key', 50)->nullable();
            $table->string('no_node_key', 50)->nullable();
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->timestamps();

            $table->unique(
                ['offer_journey_automation_version_id', 'node_key'],
                'oj_auto_nodes_key_unique'
            );
            $table->foreign('offer_journey_automation_version_id', 'oj_auto_nodes_version_fk')
                ->references('id')->on('offer_journey_automation_versions')->cascadeOnDelete();
        });

        Schema::create('offer_journey_automation_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_automation_id');
            $table->unsignedBigInteger('offer_journey_automation_version_id');
            $table->unsignedBigInteger('offer_journey_version_id')->nullable();
            $table->unsignedBigInteger('offer_journey_contact_id');
            $table->unsignedBigInteger('offer_journey_entry_id')->nullable();
            $table->string('status', 24)->default('running');
            $table->string('current_node_key', 50)->nullable();
            $table->string('exit_reason')->nullable();
            $table->string('idempotency_key', 100)->unique();
            $table->timestamp('started_at');
            $table->timestamp('next_action_at')->nullable();
            $table->timestamp('exited_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_action_at'], 'oj_auto_runs_due_idx');
            $table->foreign('offer_journey_automation_id', 'oj_auto_runs_automation_fk')
                ->references('id')->on('offer_journey_automations')->cascadeOnDelete();
            $table->foreign('offer_journey_automation_version_id', 'oj_auto_runs_version_fk')
                ->references('id')->on('offer_journey_automation_versions')->cascadeOnDelete();
            $table->foreign('offer_journey_version_id', 'oj_auto_runs_journey_version_fk')
                ->references('id')->on('offer_journey_versions')->nullOnDelete();
            $table->foreign('offer_journey_contact_id', 'oj_auto_runs_contact_fk')
                ->references('id')->on('offer_journey_contacts')->cascadeOnDelete();
            $table->foreign('offer_journey_entry_id', 'oj_auto_runs_entry_fk')
                ->references('id')->on('offer_journey_entries')->nullOnDelete();
        });

        Schema::create('offer_journey_message_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('offer_journey_id')->nullable();
            $table->unsignedBigInteger('offer_journey_contact_id')->nullable();
            $table->unsignedBigInteger('offer_journey_automation_run_id')->nullable();
            $table->string('node_key', 50)->nullable();
            $table->string('category', 24)->default('marketing');
            $table->string('status', 24)->default('queued');
            $table->string('recipient_email');
            $table->string('subject');
            $table->string('provider_message_id')->nullable();
            $table->string('idempotency_key', 100)->unique();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_test')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'category', 'sent_at'], 'oj_messages_usage_idx');
            $table->foreign('offer_journey_id', 'oj_messages_journey_fk')
                ->references('id')->on('offer_journeys')->nullOnDelete();
            $table->foreign('offer_journey_contact_id', 'oj_messages_contact_fk')
                ->references('id')->on('offer_journey_contacts')->nullOnDelete();
            $table->foreign('offer_journey_automation_run_id', 'oj_messages_run_fk')
                ->references('id')->on('offer_journey_automation_runs')->nullOnDelete();
        });

        Schema::create('offer_journey_automation_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_automation_run_id');
            $table->string('node_key', 50);
            $table->string('action_type', 40);
            $table->string('status', 24)->default('processing');
            $table->json('payload_json')->nullable();
            $table->string('idempotency_key', 100)->unique();
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('offer_journey_automation_run_id', 'oj_auto_actions_run_fk')
                ->references('id')->on('offer_journey_automation_runs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_journey_automation_actions');
        Schema::dropIfExists('offer_journey_message_deliveries');
        Schema::dropIfExists('offer_journey_automation_runs');
        Schema::dropIfExists('offer_journey_automation_nodes');
        Schema::dropIfExists('offer_journey_automation_versions');
        Schema::dropIfExists('offer_journey_automations');
    }
};
