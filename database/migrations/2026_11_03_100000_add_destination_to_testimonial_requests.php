<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonial_requests', function (Blueprint $table) {
            // Old requests and old callers remain Olithea requests.
            $table->string('destination', 16)->nullable()->default('internal');
            $table->text('google_review_url')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('google_review_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_review_url');
        });

        Schema::table('testimonial_requests', function (Blueprint $table) {
            $table->dropColumn(['destination', 'google_review_url']);
        });
    }
};
