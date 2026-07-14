<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_notes', function (Blueprint $table) {
            $table->foreignId('appointment_id')
                ->nullable()
                ->after('session_note_template_id')
                ->constrained('appointments')
                ->nullOnDelete();

            $table->index(['user_id', 'appointment_id'], 'session_notes_user_appointment_idx');
        });
    }

    public function down(): void
    {
        Schema::table('session_notes', function (Blueprint $table) {
            $table->dropIndex('session_notes_user_appointment_idx');
            $table->dropConstrainedForeignId('appointment_id');
        });
    }
};
