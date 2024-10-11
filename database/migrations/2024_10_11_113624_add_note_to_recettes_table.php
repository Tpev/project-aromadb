<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteToRecettesTable extends Migration
{
    public function up()
    {
        Schema::table('recettes', function (Blueprint $table) {
            $table->text('note')->nullable(); // Add the 'note' field
        });
    }

    public function down()
    {
        Schema::table('recettes', function (Blueprint $table) {
            $table->dropColumn('note'); // Remove the 'note' field if rolling back
        });
    }
}
