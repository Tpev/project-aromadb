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
    Schema::create('user_lesson_progress', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
        $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('user_lesson_progress');
}

};
