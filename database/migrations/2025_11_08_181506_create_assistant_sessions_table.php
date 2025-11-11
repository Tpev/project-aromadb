<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('assistant_sessions', function (Blueprint $t) {
      $t->id();
      $t->foreignId('user_id')->constrained()->cascadeOnDelete();
      $t->string('current_intent')->nullable();
      $t->json('collected_slots')->nullable();
      $t->json('missing_slots')->nullable();
      $t->boolean('awaiting_confirmation')->default(false);
      $t->timestamp('expires_at')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('assistant_sessions'); }
};
