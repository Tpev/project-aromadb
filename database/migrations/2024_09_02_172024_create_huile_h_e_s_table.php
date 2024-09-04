<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHuileHesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('huile_h_e_s', function (Blueprint $table) {
            $table->id();
            $table->string('REF');
            $table->string('NomHE');
            $table->string('NomLatin');
            $table->string('Provenance');
            $table->string('OrganeProducteur');
            $table->string('Sb');
            $table->text('Properties');
            $table->text('Indications');
            $table->text('ContreIndications')->nullable();
            $table->text('Note')->nullable();
            $table->text('Description')->nullable();
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
        Schema::dropIfExists('huile_h_e_s');
    }
}
