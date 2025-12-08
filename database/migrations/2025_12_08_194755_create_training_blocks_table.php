<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('training_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_module_id')
                ->constrained()
                ->cascadeOnDelete();

            // text / video_url / pdf
            $table->string('type');

            $table->string('title')->nullable();
            $table->longText('content')->nullable();     // texte ou URL
            $table->string('file_path')->nullable();     // PDF (pour l’instant)

            $table->json('meta')->nullable();            // pour futur quiz, etc.
            $table->integer('display_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_blocks');
    }
};
