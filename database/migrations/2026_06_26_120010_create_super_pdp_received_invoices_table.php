<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_pdp_received_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->constrained('super_pdp_connections')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('super_pdp_invoice_id');
            $table->unsignedBigInteger('super_pdp_company_id')->nullable();
            $table->string('direction', 10)->default('in');
            $table->string('external_id', 80)->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('seller_name')->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->decimal('total_with_vat', 15, 4)->nullable();
            $table->string('latest_event_code', 50)->nullable();
            $table->string('latest_event_text')->nullable();
            $table->timestamp('latest_event_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['connection_id', 'super_pdp_invoice_id'], 'spdp_inv_conn_invoice_unique');
            $table->index(['user_id', 'direction'], 'spdp_inv_user_direction_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_pdp_received_invoices');
    }
};
