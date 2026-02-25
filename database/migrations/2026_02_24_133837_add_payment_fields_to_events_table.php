<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('collect_payment')->default(false)->after('booking_required');
            $table->decimal('price', 10, 2)->nullable()->after('collect_payment'); // TTC
            $table->decimal('tax_rate', 5, 2)->default(0)->after('price');         // optional %
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['collect_payment', 'price', 'tax_rate']);
        });
    }
};