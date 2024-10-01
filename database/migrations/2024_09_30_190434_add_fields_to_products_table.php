<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('duration')->nullable()->after('price'); // Duration in minutes
            $table->boolean('can_be_booked_online')->default(false)->after('duration'); // Can be booked online
            $table->boolean('visio')->default(false)->after('can_be_booked_online'); // Visio option
            $table->boolean('adomicile')->default(false)->after('visio'); // At-home option
            $table->boolean('dans_le_cabinet')->default(false)->after('adomicile'); // In-office option
            $table->integer('max_per_day')->nullable()->after('dans_le_cabinet'); // Maximum bookings per day
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
            $table->dropColumn(['duration', 'can_be_booked_online', 'visio', 'adomicile', 'dans_le_cabinet', 'max_per_day']);
        });
    }
}
