<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Schema::create('blog_posts', function (Blueprint $table) {
			$table->id();
			$table->string('REF')->unique();
			$table->string('Title');
			$table->string('slug')->unique(); // Slug for URL
			$table->string('Tags');
			$table->text('Contents');
			$table->string('RelatedPostsREF')->nullable();
			$table->text('MetaDescription');
			$table->timestamps();
		});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
