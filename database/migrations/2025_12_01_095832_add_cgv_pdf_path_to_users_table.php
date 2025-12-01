<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_cgv_pdf_path_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cgv_pdf_path')->nullable()->after('legal_mentions');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cgv_pdf_path');
        });
    }
};
