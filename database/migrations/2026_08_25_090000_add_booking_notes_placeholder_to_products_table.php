<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'booking_notes_placeholder')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->string('booking_notes_placeholder')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'booking_notes_placeholder')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('booking_notes_placeholder');
            });
        }
    }
};
