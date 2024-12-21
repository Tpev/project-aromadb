<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('marketing_emails', function (Blueprint $table) {
        $table->id();
        $table->string('firstname');
        $table->string('lastname');
        $table->string('email')->unique();
        $table->string('tags')->nullable(); // For categorizing email lists
        $table->timestamp('last_emailed_at')->nullable(); // When the last email was sent
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_emails');
    }
};
