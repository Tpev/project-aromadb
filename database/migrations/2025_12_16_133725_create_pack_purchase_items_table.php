<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pack_purchase_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pack_purchase_id')->constrained('pack_purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->unsignedInteger('quantity_total')->default(0);
            $table->unsignedInteger('quantity_remaining')->default(0);

            $table->timestamps();

            $table->unique(['pack_purchase_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_purchase_items');
    }
};
