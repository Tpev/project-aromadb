<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('facebook_url')->nullable()->after('company_phone');
            $table->text('instagram_url')->nullable()->after('facebook_url');
            $table->text('linkedin_url')->nullable()->after('instagram_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_url',
                'instagram_url',
                'linkedin_url',
            ]);
        });
    }
};
