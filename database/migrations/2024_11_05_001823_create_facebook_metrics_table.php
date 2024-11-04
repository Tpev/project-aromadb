<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookMetricsTable extends Migration
{
    public function up()
    {
        Schema::create('facebook_metrics', function (Blueprint $table) {
            $table->id();
            $table->integer('fan_count')->nullable();
            $table->integer('followers_count')->nullable();
            $table->string('page_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('facebook_metrics');
    }
}
