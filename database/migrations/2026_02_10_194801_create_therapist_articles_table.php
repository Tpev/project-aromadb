<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapist_articles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug');

            $table->text('excerpt')->nullable();

            $table->longText('content_html')->nullable();
            $table->longText('content_json')->nullable();

            $table->string('meta_description', 180)->nullable();

            $table->string('cover_path')->nullable();

            $table->string('status')->default('draft'); // draft | published
            $table->timestamp('published_at')->nullable();

            $table->json('tags')->nullable();

            $table->unsignedInteger('reading_time')->nullable();
            $table->unsignedBigInteger('views')->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapist_articles');
    }
};
