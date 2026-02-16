<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('session_notes', function (Blueprint $table) {
            $table->foreignId('session_note_template_id')
                ->nullable()
                ->after('user_id')
                ->constrained('session_note_templates')
                ->nullOnDelete();

            $table->index(['user_id', 'session_note_template_id']);
        });
    }

    public function down(): void
    {
        Schema::table('session_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('session_note_template_id');
        });
    }
};
