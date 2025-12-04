<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->unsignedBigInteger('audience_id')
                  ->nullable()
                  ->after('background_color');

            $table->foreign('audience_id')
                  ->references('id')->on('audiences')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropForeign(['audience_id']);
            $table->dropColumn('audience_id');
        });
    }
};
