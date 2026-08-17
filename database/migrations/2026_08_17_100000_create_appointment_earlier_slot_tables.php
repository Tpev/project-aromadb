<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('appointments', 'wants_earlier_slot')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->boolean('wants_earlier_slot')->default(false)->index('appt_earlier_slot_optin_idx');
            });
        }

        if (! Schema::hasColumn('appointments', 'earlier_slot_opted_in_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->timestamp('earlier_slot_opted_in_at')->nullable();
            });
        }

        if (! Schema::hasTable('appointment_earlier_slot_opportunities')) {
            Schema::create('appointment_earlier_slot_opportunities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('released_appointment_id')->nullable();
                $table->unsignedBigInteger('claimed_appointment_id')->nullable();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('practice_location_id')->nullable();
                $table->char('location_fingerprint', 64)->nullable();
                $table->dateTime('slot_start');
                $table->unsignedInteger('duration');
                $table->string('mode', 24);
                $table->string('status', 20)->default('open');
                $table->timestamp('expires_at');
                $table->timestamp('claimed_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id', 'aeso_user_fk')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('released_appointment_id', 'aeso_released_appt_fk')->references('id')->on('appointments')->nullOnDelete();
                $table->foreign('claimed_appointment_id', 'aeso_claimed_appt_fk')->references('id')->on('appointments')->nullOnDelete();
                $table->foreign('product_id', 'aeso_product_fk')->references('id')->on('products')->cascadeOnDelete();
                $table->foreign('practice_location_id', 'aeso_location_fk')->references('id')->on('practice_locations')->nullOnDelete();
                $table->index(['user_id', 'status', 'slot_start'], 'aeso_user_status_start_idx');
                $table->index(['product_id', 'mode', 'duration'], 'aeso_compatibility_idx');
            });
        }

        if (! Schema::hasTable('appointment_earlier_slot_offers')) {
            Schema::create('appointment_earlier_slot_offers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('opportunity_id');
                $table->unsignedBigInteger('appointment_id');
                $table->text('token');
                $table->char('token_hash', 64)->unique('aeso_token_hash_uq');
                $table->string('status', 20)->default('pending');
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('claimed_at')->nullable();
                $table->timestamp('invalidated_at')->nullable();
                $table->timestamps();

                $table->foreign('opportunity_id', 'aeso_offer_opportunity_fk')
                    ->references('id')->on('appointment_earlier_slot_opportunities')->cascadeOnDelete();
                $table->foreign('appointment_id', 'aeso_offer_appointment_fk')
                    ->references('id')->on('appointments')->cascadeOnDelete();
                $table->unique(['opportunity_id', 'appointment_id'], 'aeso_offer_appointment_uq');
                $table->index(['appointment_id', 'status'], 'aeso_offer_appt_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_earlier_slot_offers');
        Schema::dropIfExists('appointment_earlier_slot_opportunities');

        if (Schema::hasColumn('appointments', 'wants_earlier_slot')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex('appt_earlier_slot_optin_idx');
                $table->dropColumn('wants_earlier_slot');
            });
        }

        if (Schema::hasColumn('appointments', 'earlier_slot_opted_in_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('earlier_slot_opted_in_at');
            });
        }
    }
};
