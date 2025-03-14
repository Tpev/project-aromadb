<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
Schema::create('metrics', function (Blueprint $table) {
    $table->id();
    
    // Instead of $table->foreignId('client_profiles'), do this:
    $table->foreignId('client_profile_id')
          ->constrained('client_profiles')
          ->onDelete('cascade');

    $table->string('name');
    $table->decimal('goal', 8, 2)->nullable();
    $table->timestamps();
});

}

public function down()
{
    Schema::dropIfExists('metrics');
}

};
