<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pack_products')) {
            Schema::table('pack_products', function (Blueprint $table) {
                if (!Schema::hasColumn('pack_products', 'installments_enabled')) {
                    $table->boolean('installments_enabled')->default(false);
                }

                if (!Schema::hasColumn('pack_products', 'allowed_installments')) {
                    $table->json('allowed_installments')->nullable();
                }
            });
        }

        if (Schema::hasTable('digital_trainings')) {
            Schema::table('digital_trainings', function (Blueprint $table) {
                if (!Schema::hasColumn('digital_trainings', 'installments_enabled')) {
                    $table->boolean('installments_enabled')->default(false);
                }

                if (!Schema::hasColumn('digital_trainings', 'allowed_installments')) {
                    $table->json('allowed_installments')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pack_products')) {
            Schema::table('pack_products', function (Blueprint $table) {
                if (Schema::hasColumn('pack_products', 'allowed_installments')) {
                    $table->dropColumn('allowed_installments');
                }

                if (Schema::hasColumn('pack_products', 'installments_enabled')) {
                    $table->dropColumn('installments_enabled');
                }
            });
        }

        if (Schema::hasTable('digital_trainings')) {
            Schema::table('digital_trainings', function (Blueprint $table) {
                if (Schema::hasColumn('digital_trainings', 'allowed_installments')) {
                    $table->dropColumn('allowed_installments');
                }

                if (Schema::hasColumn('digital_trainings', 'installments_enabled')) {
                    $table->dropColumn('installments_enabled');
                }
            });
        }
    }
};
