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
                if (!Schema::hasColumn('users', 'digital_sales_retractation_enabled')) {
                    $table->boolean('digital_sales_retractation_enabled')->default(false);
                }

                if (!Schema::hasColumn('users', 'digital_sales_retractation_label')) {
                    $table->string('digital_sales_retractation_label', 500)->nullable();
                }

                if (!Schema::hasColumn('users', 'digital_sales_retractation_url')) {
                    $table->string('digital_sales_retractation_url', 2048)->nullable();
                }
            });
        }

        if (Schema::hasTable('digital_trainings')) {
            Schema::table('digital_trainings', function (Blueprint $table) {
                if (!Schema::hasColumn('digital_trainings', 'use_global_retractation_notice')) {
                    $table->boolean('use_global_retractation_notice')->default(false);
                }
            });
        }

        if (Schema::hasTable('pack_purchases')) {
            Schema::table('pack_purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('pack_purchases', 'retractation_notice_required')) {
                    $table->boolean('retractation_notice_required')->default(false);
                }

                if (!Schema::hasColumn('pack_purchases', 'retractation_notice_accepted_at')) {
                    $table->timestamp('retractation_notice_accepted_at')->nullable();
                }

                if (!Schema::hasColumn('pack_purchases', 'retractation_notice_label_snapshot')) {
                    $table->text('retractation_notice_label_snapshot')->nullable();
                }

                if (!Schema::hasColumn('pack_purchases', 'retractation_notice_url_snapshot')) {
                    $table->string('retractation_notice_url_snapshot', 2048)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pack_purchases')) {
            Schema::table('pack_purchases', function (Blueprint $table) {
                if (Schema::hasColumn('pack_purchases', 'retractation_notice_url_snapshot')) {
                    $table->dropColumn('retractation_notice_url_snapshot');
                }

                if (Schema::hasColumn('pack_purchases', 'retractation_notice_label_snapshot')) {
                    $table->dropColumn('retractation_notice_label_snapshot');
                }

                if (Schema::hasColumn('pack_purchases', 'retractation_notice_accepted_at')) {
                    $table->dropColumn('retractation_notice_accepted_at');
                }

                if (Schema::hasColumn('pack_purchases', 'retractation_notice_required')) {
                    $table->dropColumn('retractation_notice_required');
                }
            });
        }

        if (Schema::hasTable('digital_trainings')) {
            Schema::table('digital_trainings', function (Blueprint $table) {
                if (Schema::hasColumn('digital_trainings', 'use_global_retractation_notice')) {
                    $table->dropColumn('use_global_retractation_notice');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'digital_sales_retractation_url')) {
                    $table->dropColumn('digital_sales_retractation_url');
                }

                if (Schema::hasColumn('users', 'digital_sales_retractation_label')) {
                    $table->dropColumn('digital_sales_retractation_label');
                }

                if (Schema::hasColumn('users', 'digital_sales_retractation_enabled')) {
                    $table->dropColumn('digital_sales_retractation_enabled');
                }
            });
        }
    }
};
