<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'digital_sales_retractation_document_path')) {
                    $table->string('digital_sales_retractation_document_path', 2048)->nullable()->after('digital_sales_retractation_url');
                }
            });
        }

        if (Schema::hasTable('pack_purchases')) {
            Schema::table('pack_purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('pack_purchases', 'retractation_notice_document_path_snapshot')) {
                    $table->string('retractation_notice_document_path_snapshot', 2048)->nullable()->after('retractation_notice_url_snapshot');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pack_purchases')) {
            Schema::table('pack_purchases', function (Blueprint $table) {
                if (Schema::hasColumn('pack_purchases', 'retractation_notice_document_path_snapshot')) {
                    $table->dropColumn('retractation_notice_document_path_snapshot');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'digital_sales_retractation_document_path')) {
                    $table->dropColumn('digital_sales_retractation_document_path');
                }
            });
        }
    }
};
