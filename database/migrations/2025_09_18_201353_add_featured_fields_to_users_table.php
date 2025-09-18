<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_featured_fields_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('featured_until')->nullable()->index();
            $table->unsignedTinyInteger('featured_weight')->default(0)->index(); // 0..100
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'featured_until', 'featured_weight']);
        });
    }
};
