<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->string('referral_source')->nullable()->after('source')->index();
            $table->string('expected_license_type')->nullable()->after('referral_source')->index();
            $table->string('actual_license_type')->nullable()->after('expected_license_type')->index();
        });
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropColumn([
                'referral_source',
                'expected_license_type',
                'actual_license_type',
            ]);
        });
    }
};
