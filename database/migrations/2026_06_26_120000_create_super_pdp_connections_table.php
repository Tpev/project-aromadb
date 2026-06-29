<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_pdp_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('environment', 20)->default('sandbox');
            $table->string('status', 40)->default('not_started');
            $table->boolean('receiving_invoices_enabled')->default(false);
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('token_type', 30)->nullable();
            $table->string('scope')->nullable();
            $table->unsignedBigInteger('super_pdp_company_id')->nullable();
            $table->string('super_pdp_company_name')->nullable();
            $table->string('super_pdp_company_number')->nullable();
            $table->string('super_pdp_company_number_scheme', 50)->nullable();
            $table->text('last_error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'environment'], 'spdp_conn_user_env_unique');
            $table->index(['status', 'environment'], 'spdp_conn_status_env_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_pdp_connections');
    }
};
