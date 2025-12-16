<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pack_purchases', function (Blueprint $table) {
            $table->id();

            // thérapeute propriétaire (copié pour requêtes simples)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('pack_product_id')->constrained('pack_products')->cascadeOnDelete();

            // Client (dans ton app c'est très probablement ClientProfile)
            $table->foreignId('client_profile_id')->constrained('client_profiles')->cascadeOnDelete();

            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->string('status')->default('active'); // active | exhausted | expired | cancelled
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'client_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_purchases');
    }
};
