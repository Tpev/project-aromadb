<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('tisanes', function (Blueprint $table) {
    $table->id();
    $table->string('REF')->unique();  // Unique REF field
    $table->string('NomTisane');
    $table->string('NomLatin')->nullable();
    $table->string('Provenance')->nullable();
    $table->text('Properties')->nullable();
    $table->text('Indications')->nullable();
    $table->text('ContreIndications')->nullable();
    $table->text('Description')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tisanes');
    }
};
