<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete(); // praticien
            $t->foreignId('client_profile_id')->constrained()->cascadeOnDelete();
            $t->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();

            $t->string('original_name');
            $t->string('storage_path');          // storage/app/public/... for original
            $t->unsignedInteger('pages')->nullable();

            $t->string('status')->default('draft'); // draft|sent|partially_signed|signed|expired|cancelled
            $t->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Final output
            $t->string('final_pdf_path')->nullable();
            $t->string('hash_original')->nullable();
            $t->string('hash_final')->nullable();

            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('documents'); }
};
