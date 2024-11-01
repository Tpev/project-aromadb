<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageAndBrochureToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
            $table->string('brochure')->nullable()->after('image');
        });
    }   
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image', 'brochure']);
        });
    }
}
