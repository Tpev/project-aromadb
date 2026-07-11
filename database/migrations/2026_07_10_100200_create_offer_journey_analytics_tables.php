<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_journey_campaign_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('offer_journey_id');
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->string('channel', 40);
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['offer_journey_id', 'is_active'], 'oj_campaign_journey_active_idx');
            $table->foreign('offer_journey_id', 'oj_campaign_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
        });

        Schema::create('offer_journey_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_id');
            $table->unsignedBigInteger('offer_journey_version_id')->nullable();
            $table->unsignedBigInteger('offer_journey_page_id')->nullable();
            $table->unsignedBigInteger('offer_journey_contact_id')->nullable();
            $table->unsignedBigInteger('offer_journey_entry_id')->nullable();
            $table->unsignedBigInteger('offer_journey_campaign_link_id')->nullable();
            $table->string('session_id', 64)->nullable();
            $table->string('event_name', 80);
            $table->text('url')->nullable();
            $table->text('referer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_test')->default(false);
            $table->boolean('is_bot')->default(false);
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['offer_journey_id', 'event_name', 'occurred_at'], 'oj_events_metric_idx');
            $table->index(['session_id', 'occurred_at'], 'oj_events_session_idx');
            $table->foreign('offer_journey_id', 'oj_events_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
            $table->foreign('offer_journey_version_id', 'oj_events_version_fk')
                ->references('id')->on('offer_journey_versions')->nullOnDelete();
            $table->foreign('offer_journey_page_id', 'oj_events_page_fk')
                ->references('id')->on('offer_journey_pages')->nullOnDelete();
            $table->foreign('offer_journey_contact_id', 'oj_events_contact_fk')
                ->references('id')->on('offer_journey_contacts')->nullOnDelete();
            $table->foreign('offer_journey_entry_id', 'oj_events_entry_fk')
                ->references('id')->on('offer_journey_entries')->nullOnDelete();
            $table->foreign('offer_journey_campaign_link_id', 'oj_events_campaign_fk')
                ->references('id')->on('offer_journey_campaign_links')->nullOnDelete();
        });

        Schema::create('offer_journey_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_id');
            $table->unsignedBigInteger('offer_journey_version_id')->nullable();
            $table->unsignedBigInteger('offer_journey_contact_id')->nullable();
            $table->unsignedBigInteger('offer_journey_entry_id')->nullable();
            $table->string('conversion_type', 60);
            $table->string('convertible_type')->nullable();
            $table->unsignedBigInteger('convertible_id')->nullable();
            $table->string('status', 24)->default('confirmed');
            $table->bigInteger('amount_cents')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('attribution_model', 30)->default('last_touch');
            $table->json('attribution_json')->nullable();
            $table->string('idempotency_key', 100)->unique();
            $table->timestamp('occurred_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['offer_journey_id', 'status', 'occurred_at'], 'oj_conversions_metric_idx');
            $table->index(['convertible_type', 'convertible_id'], 'oj_conversions_convertible_idx');
            $table->foreign('offer_journey_id', 'oj_conversions_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
            $table->foreign('offer_journey_version_id', 'oj_conversions_version_fk')
                ->references('id')->on('offer_journey_versions')->nullOnDelete();
            $table->foreign('offer_journey_contact_id', 'oj_conversions_contact_fk')
                ->references('id')->on('offer_journey_contacts')->nullOnDelete();
            $table->foreign('offer_journey_entry_id', 'oj_conversions_entry_fk')
                ->references('id')->on('offer_journey_entries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_journey_conversions');
        Schema::dropIfExists('offer_journey_events');
        Schema::dropIfExists('offer_journey_campaign_links');
    }
};
