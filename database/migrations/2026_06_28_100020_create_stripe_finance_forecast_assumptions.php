<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_finance_forecast_assumptions', function (Blueprint $table) {
            $table->id();
            $table->date('month')->unique();
            $table->unsignedInteger('conservative_new_customers')->default(0);
            $table->unsignedInteger('optimistic_new_customers')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_finance_forecast_assumptions');
    }
};
