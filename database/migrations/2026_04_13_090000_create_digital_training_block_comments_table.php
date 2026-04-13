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
            $table->foreignId('digital_training_id');
            $table->foreignId('training_module_id');
            $table->foreignId('training_block_id');
            $table->foreignId('digital_training_enrollment_id');
            $table->foreignId('client_profile_id')->nullable();
            $table->string('participant_name_snapshot')->nullable();
            $table->string('participant_email_snapshot')->nullable();
            $table->text('comment');
            $table->string('created_by_role', 30)->default('participant');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->foreign('digital_training_id', 'dtbc_training_fk')
                ->references('id')
                ->on('digital_trainings')
                ->cascadeOnDelete();
            $table->foreign('training_module_id', 'dtbc_module_fk')
                ->references('id')
                ->on('training_modules')
                ->cascadeOnDelete();
            $table->foreign('training_block_id', 'dtbc_block_fk')
                ->references('id')
                ->on('training_blocks')
                ->cascadeOnDelete();
            $table->foreign('digital_training_enrollment_id', 'dtbc_enrollment_fk')
                ->references('id')
                ->on('digital_training_enrollments')
                ->cascadeOnDelete();
            $table->foreign('client_profile_id', 'dtbc_client_profile_fk')
                ->references('id')
                ->on('client_profiles')
                ->nullOnDelete();

            $table->index(['training_block_id', 'digital_training_enrollment_id'], 'dtbc_block_enrollment_idx');
            $table->index(['digital_training_id', 'created_at'], 'dtbc_training_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_training_block_comments');
    }
};
