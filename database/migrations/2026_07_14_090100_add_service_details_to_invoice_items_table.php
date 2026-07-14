<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->text('description_snapshot')->nullable()->after('description');
            $table->date('service_date')->nullable()->after('description_snapshot');
            $table->date('service_period_start')->nullable()->after('service_date');
            $table->date('service_period_end')->nullable()->after('service_period_start');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'description_snapshot',
                'service_date',
                'service_period_start',
                'service_period_end',
            ]);
        });
    }
};
