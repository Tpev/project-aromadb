<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('special_availabilities', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
            ->constrained()
            ->onDelete('cascade');

        $table->date('date'); // date précise, contrairement à Availability
        $table->time('start_time');
        $table->time('end_time');

        $table->boolean('applies_to_all')->default(false);

        $table->foreignId('practice_location_id')
            ->nullable()
            ->constrained('practice_locations')
            ->onDelete('cascade');

        $table->timestamps();
    });
}

};
