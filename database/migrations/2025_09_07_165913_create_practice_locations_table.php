<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('practice_locations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('label'); // ex: "Cabinet Strasbourg"
            $t->string('address_line1');
            $t->string('address_line2')->nullable();
            $t->string('postal_code', 20)->nullable();
            $t->string('city')->nullable();
            $t->string('country', 2)->default('FR');
            $t->boolean('is_primary')->default(false);
            $t->timestamps();

            $t->index(['user_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_locations');
    }
};
