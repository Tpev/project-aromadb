<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('requires_emargement')->default(false)->after('product_id');
            $table->boolean('emargement_sent')->default(false)->after('requires_emargement');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['requires_emargement', 'emargement_sent']);
        });
    }
};
