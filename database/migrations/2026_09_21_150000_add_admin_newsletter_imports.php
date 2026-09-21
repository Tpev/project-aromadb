<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('audience_id')->nullable()->constrained()->nullOnDelete();
            $table->string('audience_name');
            $table->string('original_filename');
            $table->string('file_hash', 64);
            $table->string('status')->default('preview');
            $table->json('rows');
            $table->json('report');
            $table->timestamp('committed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'file_hash']);
        });
        Schema::create('newsletter_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_import_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email', 254);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['user_id', 'email']);
        });
        Schema::create('audience_newsletter_contact', function (Blueprint $table) {
            $table->foreignId('audience_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_contact_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['audience_id', 'newsletter_contact_id'], 'audience_newsletter_contact_primary');
        });
        Schema::table('newsletter_recipients', function (Blueprint $table) {
            $table->foreignId('newsletter_contact_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_recipients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('newsletter_contact_id');
        });
        Schema::dropIfExists('audience_newsletter_contact');
        Schema::dropIfExists('newsletter_contacts');
        Schema::dropIfExists('newsletter_imports');
    }
};
