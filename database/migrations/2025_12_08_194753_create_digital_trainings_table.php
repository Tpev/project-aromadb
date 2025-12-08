<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('digital_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->string('cover_image_path')->nullable();
            $table->json('tags')->nullable();

            $table->boolean('is_free')->default(false);
            $table->integer('price_cents')->nullable(); // prix TTC en centimes
            $table->decimal('tax_rate', 5, 2)->default(0); // ex: 20.00

            $table->enum('access_type', ['public', 'private', 'subscription'])
                  ->default('public');

            $table->enum('status', ['draft', 'published', 'archived'])
                  ->default('draft');

            $table->integer('estimated_duration_minutes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_trainings');
    }
};
