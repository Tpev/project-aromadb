<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageViewLogsTable extends Migration
{
    public function up()
    {
        Schema::create('page_view_logs', function (Blueprint $table) {
            $table->id();
            $table->string('url');         // Page URL
            $table->timestamp('viewed_at'); // Time of the view
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('page_view_logs');
    }
}
