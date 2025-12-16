<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pack_product_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pack_product_id')->constrained('pack_products')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['pack_product_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_product_items');
    }
};
