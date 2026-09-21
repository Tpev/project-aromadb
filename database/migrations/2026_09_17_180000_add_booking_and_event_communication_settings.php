<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('booking_phone_required')->default(false);
        });
        Schema::table('events', function (Blueprint $table) {
            $table->text('confirmation_email_note')->nullable();
            $table->text('reminder_email_note')->nullable();
        });
        Schema::table('pack_products', function (Blueprint $table) {
            $table->boolean('private_checkout_enabled')->default(false);
            $table->string('private_checkout_token', 64)->nullable()->unique();
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('payment_confirmation_requested_at')->nullable();
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->timestamp('therapist_notification_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', fn (Blueprint $table) => $table->dropColumn(['payment_confirmation_requested_at', 'confirmation_sent_at', 'therapist_notification_sent_at']));
        Schema::table('pack_products', function (Blueprint $table) {
            $table->dropUnique(['private_checkout_token']);
            $table->dropColumn(['private_checkout_enabled', 'private_checkout_token']);
        });
        Schema::table('events', fn (Blueprint $table) => $table->dropColumn(['confirmation_email_note', 'reminder_email_note']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('booking_phone_required'));
    }
};
