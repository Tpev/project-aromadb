<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();

            // pour filtrer rapidement par propriétaire
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // rattachement à la facture (nullable si besoin de corrections “hors facture”)
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->string('invoice_number')->index();
            $table->date('encaissement_date')->index();
            $table->string('client_name');

            // nature : service / goods (vente de biens)
            $table->enum('nature', ['service','goods'])->default('service');

            // montants
            $table->decimal('amount_ht', 10, 2);
            $table->decimal('amount_ttc', 10, 2);

            // mode de règlement
            $table->enum('payment_method', ['transfer','card','check','cash','other'])
                  ->default('transfer');

            // credit = encaissement ; debit = contre-écriture (remboursement/correction)
            $table->enum('direction', ['credit','debit'])->default('credit');

            // traçabilité
            $table->enum('source', ['payment','correction','refund'])->default('payment');
            $table->string('note')->nullable();

            // scellage applicatif (on l’alimente à la création)
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'encaissement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
