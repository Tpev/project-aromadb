<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('newsletter_opt_outs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // thérapeute
            $table->string('email');
            $table->unsignedBigInteger('newsletter_recipient_id')->nullable();
            $table->string('reason')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('newsletter_recipient_id')
                ->references('id')->on('newsletter_recipients')
                ->nullOnDelete();

            $table->index(['user_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_opt_outs');
    }
};
