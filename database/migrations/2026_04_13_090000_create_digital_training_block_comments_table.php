<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('digital_training_block_comments')) {
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
            });
        }

        Schema::table('digital_training_block_comments', function (Blueprint $table) {
            if (! $this->hasForeignKey('digital_training_block_comments', 'dtbc_training_fk')) {
                $table->foreign('digital_training_id', 'dtbc_training_fk')
                    ->references('id')
                    ->on('digital_trainings')
                    ->cascadeOnDelete();
            }

            if (! $this->hasForeignKey('digital_training_block_comments', 'dtbc_module_fk')) {
                $table->foreign('training_module_id', 'dtbc_module_fk')
                    ->references('id')
                    ->on('training_modules')
                    ->cascadeOnDelete();
            }

            if (! $this->hasForeignKey('digital_training_block_comments', 'dtbc_block_fk')) {
                $table->foreign('training_block_id', 'dtbc_block_fk')
                    ->references('id')
                    ->on('training_blocks')
                    ->cascadeOnDelete();
            }

            if (! $this->hasForeignKey('digital_training_block_comments', 'dtbc_enrollment_fk')) {
                $table->foreign('digital_training_enrollment_id', 'dtbc_enrollment_fk')
                    ->references('id')
                    ->on('digital_training_enrollments')
                    ->cascadeOnDelete();
            }

            if (! $this->hasForeignKey('digital_training_block_comments', 'dtbc_client_profile_fk')) {
                $table->foreign('client_profile_id', 'dtbc_client_profile_fk')
                    ->references('id')
                    ->on('client_profiles')
                    ->nullOnDelete();
            }

            if (! $this->hasIndex('digital_training_block_comments', 'dtbc_block_enrollment_idx')) {
                $table->index(['training_block_id', 'digital_training_enrollment_id'], 'dtbc_block_enrollment_idx');
            }

            if (! $this->hasIndex('digital_training_block_comments', 'dtbc_training_created_idx')) {
                $table->index(['digital_training_id', 'created_at'], 'dtbc_training_created_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_training_block_comments');
    }

    private function hasForeignKey(string $table, string $constraintName): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};
