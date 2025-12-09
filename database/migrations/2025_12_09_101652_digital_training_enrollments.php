<?php

// database/migrations/2025_12_09_000000_create_digital_training_enrollments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('digital_training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_training_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Optional link to your existing client profiles
            $table->foreignId('client_profile_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Snapshot of participant identity (even if client profile changes later)
            $table->string('participant_name')->nullable();
            $table->string('participant_email');

            // Access / magic link
            $table->string('access_token')->unique();
            $table->timestamp('token_expires_at')->nullable();

            // Tracking
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamp('first_accessed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Origin of access (manual/product/free/etc.)
            $table->string('source')->default('manual'); // 'manual', 'product', 'free', etc.

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_training_enrollments');
    }
};
