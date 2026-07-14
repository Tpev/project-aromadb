<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->string('provider', 40)->nullable()->after('source');
            $table->string('provider_reference', 191)->nullable()->after('provider');
            $table->unique(['user_id', 'provider', 'provider_reference'], 'receipts_user_provider_ref_uq');
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropUnique('receipts_user_provider_ref_uq');
            $table->dropColumn(['provider', 'provider_reference']);
        });
    }
};
