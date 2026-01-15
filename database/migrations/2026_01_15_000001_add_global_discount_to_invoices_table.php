<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('global_discount_type')->nullable()->after('notes'); // percent|amount|null
            $table->decimal('global_discount_value', 10, 2)->nullable()->after('global_discount_type'); // input
            $table->decimal('global_discount_amount_ht', 10, 2)->default(0)->after('global_discount_value'); // computed HT
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['global_discount_type', 'global_discount_value', 'global_discount_amount_ht']);
        });
    }
};
