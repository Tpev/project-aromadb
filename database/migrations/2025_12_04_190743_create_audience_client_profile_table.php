<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audience_client_profile', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audience_id');
            $table->unsignedBigInteger('client_profile_id');
            $table->timestamps();

            $table->foreign('audience_id')
                  ->references('id')->on('audiences')
                  ->onDelete('cascade');

            $table->foreign('client_profile_id')
                  ->references('id')->on('client_profiles')
                  ->onDelete('cascade');

            $table->unique(['audience_id', 'client_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audience_client_profile');
    }
};
