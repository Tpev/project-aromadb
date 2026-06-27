<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('company')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('source')->nullable()->index();
            $table->string('stage')->default('new')->index();
            $table->unsignedInteger('pipeline_order')->default(0);
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->unsignedTinyInteger('probability')->default(10);
            $table->date('expected_close_date')->nullable()->index();
            $table->timestamp('next_follow_up_at')->nullable()->index();
            $table->timestamp('last_touch_at')->nullable()->index();
            $table->timestamp('converted_at')->nullable()->index();
            $table->timestamp('lost_at')->nullable()->index();
            $table->string('lost_reason')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['stage', 'pipeline_order']);
            $table->index(['created_at', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
    }
};
