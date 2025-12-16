<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pack_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            // Prix du pack (TTC/HT selon ta logique existante, ici comme Product: price + tax_rate)
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);

            $table->boolean('is_active')->default(true);

            // Portail (aligné sur Product)
            $table->boolean('visible_in_portal')->default(true);
            $table->boolean('price_visible_in_portal')->default(true);

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_products');
    }
};
