<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');         // Internal name
            $table->string('subject');       // Email subject
            $table->string('preheader')->nullable();
            $table->string('from_name');
            $table->string('from_email');

            $table->longText('content_json')->nullable(); // Blocks definition as JSON

            $table->string('status')->default('draft');   // draft, scheduled, sent

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->unsignedInteger('recipients_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletters');
    }
};
