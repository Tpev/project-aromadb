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
    Schema::create('chapters', function (Blueprint $table) {
        $table->id();
        $table->foreignId('training_id')->constrained()->cascadeOnDelete();
        $table->string('title');
        $table->integer('position')->default(1);
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('chapters');
}

};
