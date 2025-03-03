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
    Schema::create('lessons', function (Blueprint $table) {
        $table->id();
        $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
        $table->string('title');
        $table->longText('content')->nullable();
        $table->integer('position')->default(1);
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('lessons');
}

};
