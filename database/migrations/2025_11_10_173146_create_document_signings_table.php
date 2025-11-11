<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('document_signings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('document_id')->constrained()->cascadeOnDelete();

            $t->string('token', 128)->unique();
            $t->string('current_role')->default('client'); // client|therapist
            $t->string('status')->default('sent');         // sent|partially_signed|signed|expired|cancelled
            $t->timestamp('expires_at');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('document_signings'); }
};
