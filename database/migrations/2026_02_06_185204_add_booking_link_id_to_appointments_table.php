<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('booking_link_id')
                ->nullable()
                ->after('user_id') // adjust if you prefer another spot
                ->constrained('booking_links')
                ->nullOnDelete();

            $table->index('booking_link_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('booking_link_id');
        });
    }
};
