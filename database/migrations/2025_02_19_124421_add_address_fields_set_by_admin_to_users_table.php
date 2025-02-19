<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressFieldsSetByAdminToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Address fields set by admin
            $table->string('street_address_setByAdmin')->nullable()->after('company_address');
            $table->string('address_line2_setByAdmin')->nullable()->after('street_address_setByAdmin');
            $table->string('city_setByAdmin')->nullable()->after('address_line2_setByAdmin');
            $table->string('state_setByAdmin')->nullable()->after('city_setByAdmin');
            $table->string('postal_code_setByAdmin')->nullable()->after('state_setByAdmin');
            $table->string('country_setByAdmin')->nullable()->after('postal_code_setByAdmin');
            // Optional geolocation fields
            $table->decimal('latitude_setByAdmin', 10, 7)->nullable()->after('country_setByAdmin');
            $table->decimal('longitude_setByAdmin', 10, 7)->nullable()->after('latitude_setByAdmin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'street_address_setByAdmin',
                'address_line2_setByAdmin',
                'city_setByAdmin',
                'state_setByAdmin',
                'postal_code_setByAdmin',
                'country_setByAdmin',
                'latitude_setByAdmin',
                'longitude_setByAdmin',
            ]);
        });
    }
}
