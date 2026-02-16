<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('session_note_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->longText('content')->nullable(); // HTML Quill purifié

            $table->timestamps();

            $table->index(['user_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_note_templates');
    }
};
