<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
	public function up()
	{
		Schema::create('favorites', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who added to favorites
			$table->morphs('favoritable'); // Polymorphic relationship for HE, HV, Recette
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('favorites');
	}

};
