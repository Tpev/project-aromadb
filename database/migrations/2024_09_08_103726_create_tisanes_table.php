<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTisanesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tisanes', function (Blueprint $table) {
            $table->id();
            $table->string('REF');
            $table->string('NomTisane');
            $table->string('NomLatin');
            $table->string('Provenance');
            $table->string('OrganeProducteur');
            $table->string('Sb');
            $table->text('Properties');
            $table->text('Indications');
            $table->text('ContreIndications')->nullable();
            $table->text('Note')->nullable();
            $table->text('Description')->nullable();
            $table->string('slug')->unique(); // Slug for SEO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tisanes');
    }
}
