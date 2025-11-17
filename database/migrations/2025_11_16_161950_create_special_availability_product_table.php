<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('special_availability_product', function (Blueprint $table) {
        $table->id();

        $table->foreignId('special_availability_id')
            ->constrained('special_availabilities')
            ->onDelete('cascade');

        $table->foreignId('product_id')
            ->constrained()
            ->onDelete('cascade');

        $table->timestamps();

        $table->unique(
            ['special_availability_id', 'product_id'],
            'special_availability_product_unique'
        );
    });
}

public function down(): void
{
    Schema::dropIfExists('special_availability_product');
}

};
