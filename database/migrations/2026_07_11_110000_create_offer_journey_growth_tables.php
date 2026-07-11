<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_journey_reusable_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('type', 40);
            $table->json('content_json');
            $table->timestamps();

            $table->index(['user_id', 'type'], 'oj_reusable_sections_user_type_idx');
        });

        Schema::create('offer_journey_form_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_journey_contact_id');
            $table->unsignedBigInteger('offer_journey_id');
            $table->unsignedBigInteger('offer_journey_page_version_id')->nullable();
            $table->string('field_name', 80);
            $table->string('field_label');
            $table->string('field_type', 32);
            $table->string('purpose');
            $table->json('value_json')->nullable();
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->index(['offer_journey_contact_id', 'answered_at'], 'oj_form_answers_contact_idx');
            $table->foreign('offer_journey_contact_id', 'oj_form_answers_contact_fk')
                ->references('id')->on('offer_journey_contacts')->cascadeOnDelete();
            $table->foreign('offer_journey_id', 'oj_form_answers_journey_fk')
                ->references('id')->on('offer_journeys')->cascadeOnDelete();
            $table->foreign('offer_journey_page_version_id', 'oj_form_answers_page_version_fk')
                ->references('id')->on('offer_journey_page_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_journey_form_answers');
        Schema::dropIfExists('offer_journey_reusable_sections');
    }
};
