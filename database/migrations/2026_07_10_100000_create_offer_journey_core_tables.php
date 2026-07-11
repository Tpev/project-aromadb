<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_journeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('objective', 40);
            $table->string('status', 24)->default('draft');
            $table->string('source_type', 60)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('primary_conversion_type', 60)->nullable();
            $table->string('timezone', 80)->default('Europe/Paris');
            $table->boolean('show_on_profile')->default(false);
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug'], 'oj_user_slug_unique');
            $table->index(['user_id', 'status'], 'oj_user_status_idx');
            $table->index(['source_type', 'source_id'], 'oj_source_idx');
        });

        Schema::create('offer_journey_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_id');
            $table->unsignedInteger('version_number');
            $table->unsignedBigInteger('published_by_user_id')->nullable();
            $table->unsignedInteger('schema_version')->default(1);
            $table->json('snapshot_json')->nullable();
            $table->timestamp('published_at');
            $table->timestamps();

            $table->unique(['offer_journey_id', 'version_number'], 'oj_versions_number_unique');
            $table->foreign('offer_journey_id', 'oj_versions_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
            $table->foreign('published_by_user_id', 'oj_versions_publisher_fk')
                ->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('offer_journey_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_id');
            $table->string('name');
            $table->string('slug');
            $table->string('type', 40);
            $table->unsignedInteger('position')->default(0);
            $table->json('draft_content_json')->nullable();
            $table->json('theme_json')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_indexable')->default(false);
            $table->string('validation_state', 24)->default('incomplete');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['offer_journey_id', 'slug'], 'oj_pages_slug_unique');
            $table->index(['offer_journey_id', 'position'], 'oj_pages_position_idx');
            $table->foreign('offer_journey_id', 'oj_pages_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
        });

        Schema::create('offer_journey_page_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_version_id');
            $table->unsignedBigInteger('offer_journey_page_id');
            $table->string('slug');
            $table->string('type', 40);
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('schema_version')->default(1);
            $table->json('content_json')->nullable();
            $table->json('theme_json')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_indexable')->default(false);
            $table->string('content_hash', 64);
            $table->timestamps();

            $table->unique(
                ['offer_journey_version_id', 'offer_journey_page_id'],
                'oj_page_versions_page_unique'
            );
            $table->foreign('offer_journey_version_id', 'oj_page_versions_version_fk')
                ->references('id')->on('offer_journey_versions')->cascadeOnDelete();
            $table->foreign('offer_journey_page_id', 'oj_page_versions_page_fk')
                ->references('id')->on('offer_journey_pages')->cascadeOnDelete();
        });

        Schema::create('offer_journey_transitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_id');
            $table->unsignedBigInteger('from_page_id');
            $table->unsignedBigInteger('to_page_id')->nullable();
            $table->string('trigger', 60)->default('primary_cta');
            $table->json('condition_json')->nullable();
            $table->string('external_action', 80)->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_fallback')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['offer_journey_id', 'from_page_id'], 'oj_transitions_from_idx');
            $table->foreign('offer_journey_id', 'oj_transitions_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
            $table->foreign('from_page_id', 'oj_transitions_from_fk')
                ->references('id')->on('offer_journey_pages')->cascadeOnDelete();
            $table->foreign('to_page_id', 'oj_transitions_to_fk')
                ->references('id')->on('offer_journey_pages')->nullOnDelete();
        });

        Schema::create('offer_journey_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_id');
            $table->unsignedBigInteger('offer_journey_page_id');
            $table->string('submit_label')->default('Continuer');
            $table->text('success_message')->nullable();
            $table->text('privacy_text')->nullable();
            $table->string('marketing_consent_mode', 24)->default('optional');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('offer_journey_page_id', 'oj_forms_page_unique');
            $table->foreign('offer_journey_id', 'oj_forms_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
            $table->foreign('offer_journey_page_id', 'oj_forms_page_fk')
                ->references('id')->on('offer_journey_pages')->cascadeOnDelete();
        });

        Schema::create('offer_journey_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_form_id');
            $table->string('name', 80);
            $table->string('label');
            $table->string('type', 32);
            $table->boolean('is_required')->default(false);
            $table->json('options_json')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('purpose')->nullable();
            $table->timestamps();

            $table->unique(['offer_journey_form_id', 'name'], 'oj_form_fields_name_unique');
            $table->foreign('offer_journey_form_id', 'oj_form_fields_form_fk')
                ->references('id')->on('offer_journey_forms')->cascadeOnDelete();
        });

        Schema::create('offer_journey_slug_redirects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_id');
            $table->unsignedBigInteger('offer_journey_page_id')->nullable();
            $table->string('scope_type', 16)->default('journey');
            $table->string('old_slug');
            $table->string('new_slug');
            $table->timestamps();

            $table->unique(['offer_journey_id', 'scope_type', 'old_slug'], 'oj_slug_redirect_unique');
            $table->foreign('offer_journey_id', 'oj_slug_redirect_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
            $table->foreign('offer_journey_page_id', 'oj_slug_redirect_page_fk')
                ->references('id')->on('offer_journey_pages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_journey_slug_redirects');
        Schema::dropIfExists('offer_journey_form_fields');
        Schema::dropIfExists('offer_journey_forms');
        Schema::dropIfExists('offer_journey_transitions');
        Schema::dropIfExists('offer_journey_page_versions');
        Schema::dropIfExists('offer_journey_pages');
        Schema::dropIfExists('offer_journey_versions');
        Schema::dropIfExists('offer_journeys');
    }
};
