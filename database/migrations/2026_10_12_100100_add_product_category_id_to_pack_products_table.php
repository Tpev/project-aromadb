<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pack_products', function (Blueprint $table) {
            $table->foreignId('product_category_id')->nullable()->after('user_id')
                ->constrained('product_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pack_products', fn (Blueprint $table) => $table->dropConstrainedForeignId('product_category_id'));
    }
};
