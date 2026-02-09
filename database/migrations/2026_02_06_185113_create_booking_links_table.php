<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_links', function (Blueprint $table) {
            $table->id();

            // Therapist owner
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Public token in URL (/b/{token})
            $table->string('token', 64)->unique();

            // Friendly label for the therapist/admin
            $table->string('name')->nullable();

            // List of products allowed by this link
            // Example: [12, 15, 18]
            $table->json('allowed_product_ids')->nullable();

            // Optional controls
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses_count')->default(0);
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            // Helpful indexes
            $table->index(['user_id', 'is_enabled']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_links');
    }
};
