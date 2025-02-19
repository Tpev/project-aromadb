<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifiedAndVisibleAnnuarireAdminSetToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the two boolean fields after the 'slug' column (adjust position as needed)
            $table->boolean('verified')->default(false)->after('slug');
            $table->boolean('visible_annuarire_admin_set')->default(false)->after('verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verified', 'visible_annuarire_admin_set']);
        });
    }
}
