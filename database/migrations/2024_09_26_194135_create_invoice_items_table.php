<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Exécute les migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id'); // Clé étrangère vers invoices
            $table->unsignedBigInteger('product_id')->nullable(); // Clé étrangère vers products
            $table->string('description'); // Description de l'article
            $table->integer('quantity'); // Quantité
            $table->decimal('unit_price', 10, 2); // Prix unitaire
            $table->decimal('total_price', 10, 2); // Prix total (quantity * unit_price)
            $table->timestamps();

            // Contraintes de clés étrangères
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Annule les migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
}
