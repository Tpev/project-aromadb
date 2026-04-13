<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_training_block_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_module_id')->constrained('training_modules')->cascadeOnDelete();
            $table->foreignId('training_block_id')->constrained('training_blocks')->cascadeOnDelete();
            $table->foreignId('digital_training_enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('participant_name_snapshot')->nullable();
            $table->string('participant_email_snapshot')->nullable();
            $table->text('comment');
            $table->string('created_by_role', 30)->default('participant');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['training_block_id', 'digital_training_enrollment_id'], 'dtbc_block_enrollment_idx');
            $table->index(['digital_training_id', 'created_at'], 'dtbc_training_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_training_block_comments');
    }
};
