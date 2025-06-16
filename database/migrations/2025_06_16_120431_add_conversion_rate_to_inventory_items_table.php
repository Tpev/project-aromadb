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
    Schema::table('inventory_items', function (Blueprint $table) {
        $table->decimal('drop_to_ml_ratio', 5, 2)->default(20); // 20 drops per ml by default
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            //
        });
    }
};
