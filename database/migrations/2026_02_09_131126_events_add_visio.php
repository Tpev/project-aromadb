<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('event_type')->default('in_person'); // in_person | visio
            $table->string('visio_provider')->nullable();       // external | aromamade
            $table->text('visio_url')->nullable();              // lien externe (ou futur)
            $table->string('visio_token')->nullable()->unique();// lien AromaMade (token)
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['visio_token']);
            $table->dropColumn(['event_type', 'visio_provider', 'visio_url', 'visio_token']);
        });
    }
};
