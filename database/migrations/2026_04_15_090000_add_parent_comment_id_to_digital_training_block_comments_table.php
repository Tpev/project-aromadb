<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_training_block_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('digital_training_block_comments', 'parent_comment_id')) {
                $table->foreignId('parent_comment_id')
                    ->nullable()
                    ->after('client_profile_id');

                $table->foreign('parent_comment_id', 'dtbc_parent_fk')
                    ->references('id')
                    ->on('digital_training_block_comments')
                    ->cascadeOnDelete();

                $table->index(['training_block_id', 'parent_comment_id'], 'dtbc_block_parent_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('digital_training_block_comments', function (Blueprint $table) {
            if (Schema::hasColumn('digital_training_block_comments', 'parent_comment_id')) {
                $table->dropForeign('dtbc_parent_fk');
                $table->dropIndex('dtbc_block_parent_idx');
                $table->dropColumn('parent_comment_id');
            }
        });
    }
};
