<?php

use Illuminate\Database\Migrations\Migration; 
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $t) {
            $t->boolean('external')->default(false)->after('google_event_id');
            $t->foreignId('client_profile_id')->nullable()->change();
            $t->foreignId('product_id')->nullable()->change();
            $t->integer('duration')->nullable()->change();
        });
    }

    public function down(): void
    {
        // rollback rapide : remet NOT NULL, supprime external
        Schema::table('appointments', function (Blueprint $t) {
            $t->dropColumn('external');
            // à toi de remettre NOT NULL si besoin
        });
    }
};
