<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_pdp_oauth_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('environment', 20);
            $table->char('state_hash', 64)->unique('spdp_oauth_state_unique');
            $table->boolean('receive_in_app')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(
                ['user_id', 'environment', 'expires_at'],
                'spdp_oauth_user_env_expiry_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_pdp_oauth_attempts');
    }
};
