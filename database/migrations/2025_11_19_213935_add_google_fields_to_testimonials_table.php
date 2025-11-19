<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (!Schema::hasColumn('testimonials', 'source')) {
                $table->string('source')->default('internal')->after('testimonial'); // internal | google
            }

            if (!Schema::hasColumn('testimonials', 'external_review_id')) {
                $table->string('external_review_id')->nullable()->after('source')->index();
            }

            if (!Schema::hasColumn('testimonials', 'rating')) {
                $table->unsignedTinyInteger('rating')->nullable()->after('external_review_id');
            }

            if (!Schema::hasColumn('testimonials', 'reviewer_name')) {
                $table->string('reviewer_name')->nullable()->after('rating');
            }

            if (!Schema::hasColumn('testimonials', 'reviewer_profile_photo_url')) {
                $table->string('reviewer_profile_photo_url')->nullable()->after('reviewer_name');
            }

            if (!Schema::hasColumn('testimonials', 'visible_on_public_profile')) {
                $table->boolean('visible_on_public_profile')->default(true)->after('reviewer_profile_photo_url');
            }

            if (!Schema::hasColumn('testimonials', 'external_created_at')) {
                $table->timestamp('external_created_at')->nullable()->after('visible_on_public_profile');
            }

            if (!Schema::hasColumn('testimonials', 'external_updated_at')) {
                $table->timestamp('external_updated_at')->nullable()->after('external_created_at');
            }

            if (!Schema::hasColumn('testimonials', 'owner_reply')) {
                $table->text('owner_reply')->nullable()->after('external_updated_at');
            }

            if (!Schema::hasColumn('testimonials', 'owner_reply_updated_at')) {
                $table->timestamp('owner_reply_updated_at')->nullable()->after('owner_reply');
            }
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'external_review_id',
                'rating',
                'reviewer_name',
                'reviewer_profile_photo_url',
                'visible_on_public_profile',
                'external_created_at',
                'external_updated_at',
                'owner_reply',
                'owner_reply_updated_at',
            ]);
        });
    }
};
