<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_pack_purchase_id_to_invoices_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('pack_purchase_id')
                ->nullable()
                ->after('client_profile_id')
                ->constrained('pack_purchases')
                ->nullOnDelete();

            $table->index(['user_id', 'pack_purchase_id']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pack_purchase_id');
        });
    }
};
