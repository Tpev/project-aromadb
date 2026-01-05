<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) invoice_number nullable (manual entries)
        DB::statement("ALTER TABLE receipts MODIFY invoice_number VARCHAR(255) NULL");

        // 2) client_name nullable (manual entries)
        DB::statement("ALTER TABLE receipts MODIFY client_name VARCHAR(255) NULL");

        // 3) nature enum: add 'other' (pour tout ce qui n'est pas service/goods)
        DB::statement("ALTER TABLE receipts MODIFY nature ENUM('service','goods','other') NOT NULL DEFAULT 'service'");

        // 4) source enum: add 'manual'
        DB::statement("ALTER TABLE receipts MODIFY source ENUM('payment','correction','refund','manual') NOT NULL DEFAULT 'payment'");
    }

    public function down(): void
    {
        // ⚠️ Down : on revient au schéma strict d’origine.
        // Si tu as des lignes 'manual' ou 'other', ce rollback pourrait échouer.
        DB::statement("ALTER TABLE receipts MODIFY source ENUM('payment','correction','refund') NOT NULL DEFAULT 'payment'");
        DB::statement("ALTER TABLE receipts MODIFY nature ENUM('service','goods') NOT NULL DEFAULT 'service'");

        DB::statement("ALTER TABLE receipts MODIFY client_name VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE receipts MODIFY invoice_number VARCHAR(255) NOT NULL");
    }
};
