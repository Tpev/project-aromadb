<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 64);
            $table->string('message');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'created_at'], 'invoice_activity_invoice_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_activity_logs');
    }
};
