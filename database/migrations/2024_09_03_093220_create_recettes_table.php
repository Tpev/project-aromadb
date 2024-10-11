<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecettesTable extends Migration
{
    public function up()
    {
        Schema::create('recettes', function (Blueprint $table) {
            $table->id();
            $table->string('REF')->unique();
            $table->string('NomRecette');
            $table->string('slug')->unique(); // Add this field
            $table->string('TypeApplication');
            $table->text('IngredientsHE');
            $table->text('IngredientsHV');
            $table->text('IngredientsTisane');
            $table->text('Explication');
			$table->text('note')->nullable(); // Add the 'note' field
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recettes');
    }
}
