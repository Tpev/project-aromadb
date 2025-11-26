<?php

// database/migrations/2025_11_26_000000_add_price_visible_in_portal_to_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('price_visible_in_portal')
                  ->default(true)
                  ->after('price'); // adapte la position si tu veux
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price_visible_in_portal');
        });
    }
};
