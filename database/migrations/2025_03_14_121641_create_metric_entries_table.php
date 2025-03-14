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
    Schema::create('metric_entries', function (Blueprint $table) {
        $table->id();
        $table->foreignId('metric_id')->constrained()->onDelete('cascade');
        $table->date('entry_date');
        $table->decimal('value', 8, 2); // the numerical value
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('metric_entries');
}

};
