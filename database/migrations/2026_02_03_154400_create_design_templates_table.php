<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('design_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('category')->default('general'); // event, promo, quote, etc.
            $table->string('format_id'); // ig_square, story, etc.

            $table->longText('konva_json'); // stage.toJSON()

            $table->string('preview_path')->nullable(); // storage path (public)

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['format_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_templates');
    }
};
