<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_clients', function (Blueprint $table) {
            $table->id();
            
            // Propriétaire (thérapeute)
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Infos légales de l'entreprise
            $table->string('name'); // Raison sociale
            $table->string('trade_name')->nullable(); // Nom commercial
            $table->string('siret')->nullable();
            $table->string('vat_number')->nullable();

            // Adresse de facturation
            $table->string('billing_address')->nullable();
            $table->string('billing_zip')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_country')->nullable();

            // Contact facturation
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();

            // Contact principal (humain) – indicatif, le vrai profil reste un ClientProfile
            $table->string('main_contact_first_name')->nullable();
            $table->string('main_contact_last_name')->nullable();
            $table->string('main_contact_email')->nullable();
            $table->string('main_contact_phone')->nullable();

            // Notes internes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_clients');
    }
};
