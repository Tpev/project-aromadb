<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Stocke le chemin public disk (ex: invoice_logos/{userId}/logo_xxx.png)
            $table->string('invoice_logo_path')->nullable()->after('cgv_pdf_path');

            // Couleur primaire (#RRGGBB)
            $table->string('invoice_primary_color', 7)->nullable()->after('invoice_logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['invoice_logo_path', 'invoice_primary_color']);
        });
    }
};
