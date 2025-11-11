<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('document_sign_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('document_id')->constrained()->cascadeOnDelete();

            $t->string('role'); // client|therapist
            $t->timestamp('signed_at');
            $t->string('signer_ip')->nullable();
            $t->text('signer_user_agent')->nullable();
            $t->string('signature_image_path')->nullable(); // public disk path to PNG

            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('document_sign_events'); }
};
