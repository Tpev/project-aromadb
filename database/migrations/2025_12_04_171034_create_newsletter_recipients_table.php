<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('newsletter_recipients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('newsletter_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('client_profile_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('email');
            $table->string('status')->default('pending'); // pending, sent, bounced, unsubscribed

            $table->string('unsubscribe_token')->nullable()->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_recipients');
    }
};
