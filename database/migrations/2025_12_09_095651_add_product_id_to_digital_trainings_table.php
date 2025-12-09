<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_trainings', function (Blueprint $table) {
            // Lien optionnel vers un produit (pour le pricing / facturation)
            $table->foreignId('product_id')
                ->nullable()
                ->after('access_type')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('digital_trainings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
