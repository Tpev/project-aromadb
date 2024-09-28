<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxRateToProductsTable extends Migration
{
    /**
     * Exécute les migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Ajoute la colonne tax_rate avec une valeur par défaut de 0
            $table->decimal('tax_rate', 5, 2)->default(0)->after('price');
        });
    }

    /**
     * Annule les migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Supprime la colonne tax_rate
            $table->dropColumn('tax_rate');
        });
    }
}
