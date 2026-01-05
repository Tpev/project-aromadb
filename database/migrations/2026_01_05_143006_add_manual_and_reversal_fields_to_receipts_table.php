<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {

            // ✅ Si tu n’as pas encore record_number
            if (!Schema::hasColumn('receipts', 'record_number')) {
                $table->unsignedBigInteger('record_number')->nullable()->after('invoice_id')->index();
            }

            // ✅ Pour la contre-passation
            if (!Schema::hasColumn('receipts', 'is_reversal')) {
                $table->boolean('is_reversal')->default(false)->after('note');
            }

            if (!Schema::hasColumn('receipts', 'reversal_of_id')) {
                $table->unsignedBigInteger('reversal_of_id')->nullable()->after('is_reversal')->index();
            }

            // ✅ Source (auto/manual/correction/etc.)
            if (!Schema::hasColumn('receipts', 'source')) {
                $table->string('source', 40)->default('auto')->after('direction');
            } else {
                // si colonne existe mais pas de taille / défaut correct, on ne force pas ici
            }

            // ✅ Locked timestamp (immuabilité / scellement)
            if (!Schema::hasColumn('receipts', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('reversal_of_id');
            }

        });

        // FK séparées pour éviter certaines erreurs MySQL lors de Schema::table
        Schema::table('receipts', function (Blueprint $table) {

            // reversal_of_id -> receipts.id
            // On vérifie la présence de la colonne avant de créer la FK
            if (Schema::hasColumn('receipts', 'reversal_of_id')) {
                // Nom de contrainte explicite pour pouvoir drop facilement
                $table->foreign('reversal_of_id', 'receipts_reversal_of_id_fk')
                    ->references('id')
                    ->on('receipts')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {

            // Drop FK d'abord
            if (Schema::hasColumn('receipts', 'reversal_of_id')) {
                // MySQL: il faut dropForeign par nom de contrainte
                try {
                    $table->dropForeign('receipts_reversal_of_id_fk');
                } catch (\Throwable $e) {
                    // fallback si Laravel a auto-nommé différemment sur ton env
                    try { $table->dropForeign(['reversal_of_id']); } catch (\Throwable $e2) {}
                }
            }

            if (Schema::hasColumn('receipts', 'reversal_of_id')) {
                $table->dropColumn('reversal_of_id');
            }
            if (Schema::hasColumn('receipts', 'is_reversal')) {
                $table->dropColumn('is_reversal');
            }
            if (Schema::hasColumn('receipts', 'locked_at')) {
                $table->dropColumn('locked_at');
            }
            if (Schema::hasColumn('receipts', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('receipts', 'record_number')) {
                $table->dropColumn('record_number');
            }
        });
    }
};
