<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('line_discount_type')->nullable()->after('tax_rate'); // percent|amount|null
            $table->decimal('line_discount_value', 10, 2)->nullable()->after('line_discount_type'); // input
            $table->decimal('line_discount_amount_ht', 10, 2)->default(0)->after('line_discount_value'); // computed HT

            $table->decimal('global_discount_amount_ht', 10, 2)->default(0)->after('line_discount_amount_ht'); // computed HT allocation

            $table->decimal('total_price_before_discount', 10, 2)->nullable()->after('global_discount_amount_ht'); // optional display
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'line_discount_type',
                'line_discount_value',
                'line_discount_amount_ht',
                'global_discount_amount_ht',
                'total_price_before_discount',
            ]);
        });
    }
};
