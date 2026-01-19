<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Allow invoices without a person client when billed to a company
            if (Schema::hasColumn('invoices', 'client_profile_id')) {
                $table->unsignedBigInteger('client_profile_id')->nullable()->change();
            }

            // New: direct corporate billing target
            if (!Schema::hasColumn('invoices', 'corporate_client_id')) {
                $table->unsignedBigInteger('corporate_client_id')->nullable()->after('client_profile_id');
                $table->foreign('corporate_client_id')
                      ->references('id')
                      ->on('corporate_clients')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'corporate_client_id')) {
                // Drop FK first (name can vary), so drop by columns
                $table->dropForeign(['corporate_client_id']);
                $table->dropColumn('corporate_client_id');
            }

            // Revert client_profile_id to NOT NULL (only if you really want to)
            if (Schema::hasColumn('invoices', 'client_profile_id')) {
                $table->unsignedBigInteger('client_profile_id')->nullable(false)->change();
            }
        });
    }
};
