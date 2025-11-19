<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            // On rend ces colonnes optionnelles pour supporter les avis externes (Google)
            $table->unsignedBigInteger('client_profile_id')->nullable()->change();
            $table->unsignedBigInteger('testimonial_request_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            // Si tu veux vraiment revenir en arrière un jour
            $table->unsignedBigInteger('client_profile_id')->nullable(false)->change();
            $table->unsignedBigInteger('testimonial_request_id')->nullable(false)->change();
        });
    }
};
