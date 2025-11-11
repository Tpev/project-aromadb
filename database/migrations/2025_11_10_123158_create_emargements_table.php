<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('emargements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('therapist_id')->constrained('users')->cascadeOnDelete();
            $table->string('client_email')->index();
            $table->string('token', 80)->unique()->index();
            $table->timestamp('expires_at')->index();
            $table->enum('status', ['pending','signed','expired'])->default('pending')->index();
            $table->timestamp('signed_at')->nullable();
            $table->string('signer_ip')->nullable();
            $table->string('signature_image_path')->nullable(); // public/storage path
            $table->string('pdf_path')->nullable();              // generated evidence PDF
            $table->json('meta')->nullable();                    // snapshot (therapist/client/product/appointment)
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('emargements');
    }
};
