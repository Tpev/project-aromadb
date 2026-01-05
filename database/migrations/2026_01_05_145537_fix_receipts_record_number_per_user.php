<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 0) Assurer l'existence de record_number (si déjà présent : no-op)
         */
        if (!Schema::hasColumn('receipts', 'record_number')) {
            Schema::table('receipts', function (Blueprint $table) {
                $table->unsignedInteger('record_number')->nullable()->after('id');
            });
        } else {
            // Si la colonne existe mais est NOT NULL, on la rend temporairement nullable pour renumérotation propre
            // (au cas où certaines lignes ont NULL)
            try {
                DB::statement("ALTER TABLE receipts MODIFY record_number INT UNSIGNED NULL");
            } catch (\Throwable $e) {
                // ignore (selon driver/config, peut échouer si déjà OK)
            }
        }

        /**
         * 1) Backfill / Repair : record_number = 1..N par user
         * Tri stable : encaissement_date, created_at, id
         */
        $userIds = DB::table('receipts')
            ->select('user_id')
            ->distinct()
            ->orderBy('user_id')
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $ids = DB::table('receipts')
                ->where('user_id', $userId)
                ->orderBy('encaissement_date')
                ->orderBy('created_at')
                ->orderBy('id')
                ->pluck('id');

            $i = 1;
            foreach ($ids as $id) {
                DB::table('receipts')
                    ->where('id', $id)
                    ->update(['record_number' => $i]);
                $i++;
            }
        }

        /**
         * 2) Enforce NOT NULL sur record_number
         */
        DB::statement("ALTER TABLE receipts MODIFY record_number INT UNSIGNED NOT NULL");

        /**
         * 3) Ajouter UNIQUE(user_id, record_number)
         * On supprime un éventuel index existant portant le même nom (si migrations précédentes)
         */
        $indexName = 'receipts_user_id_record_number_unique';

        // Drop unique s'il existe déjà sous ce nom
        $existing = DB::select("SHOW INDEX FROM receipts WHERE Key_name = ?", [$indexName]);
        if (!empty($existing)) {
            Schema::table('receipts', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }

        // Créer l'unique (user_id, record_number)
        Schema::table('receipts', function (Blueprint $table) use ($indexName) {
            $table->unique(['user_id', 'record_number'], $indexName);
        });

        /**
         * 4) (Optionnel mais utile) Index de lookup rapide
         * Si tu veux filtrer souvent par user + date
         */
        $idx = DB::select("SHOW INDEX FROM receipts WHERE Key_name = 'receipts_user_id_encaissement_date_idx'");
        if (empty($idx)) {
            Schema::table('receipts', function (Blueprint $table) {
                $table->index(['user_id', 'encaissement_date'], 'receipts_user_id_encaissement_date_idx');
            });
        }
    }

    public function down(): void
    {
        // On enlève juste les contraintes ajoutées
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropUnique('receipts_user_id_record_number_unique');
            $table->dropIndex('receipts_user_id_encaissement_date_idx');
        });

        // On ne supprime pas record_number en down pour éviter de casser l'app,
        // mais si tu veux vraiment rollback "hard", décommente :
        /*
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn('record_number');
        });
        */
    }
};
