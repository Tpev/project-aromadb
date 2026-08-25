<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $userColumns = [
            'booking_schedule_mode' => ! Schema::hasColumn('users', 'booking_schedule_mode'),
            'booking_slot_interval_minutes' => ! Schema::hasColumn('users', 'booking_slot_interval_minutes'),
            'information_requests_enabled' => ! Schema::hasColumn('users', 'information_requests_enabled'),
        ];
        if (in_array(true, $userColumns, true)) {
            Schema::table('users', function (Blueprint $table) use ($userColumns) {
                if ($userColumns['booking_schedule_mode']) {
                    $table->string('booking_schedule_mode', 20)->nullable();
                }
                if ($userColumns['booking_slot_interval_minutes']) {
                    $table->unsignedSmallInteger('booking_slot_interval_minutes')->nullable();
                }
                if ($userColumns['information_requests_enabled']) {
                    $table->boolean('information_requests_enabled')->default(true);
                }
            });
        }

        $productColumns = [
            'preparation_time_minutes' => ! Schema::hasColumn('products', 'preparation_time_minutes'),
            'buffer_time_after_minutes' => ! Schema::hasColumn('products', 'buffer_time_after_minutes'),
            'confirmation_email_note' => ! Schema::hasColumn('products', 'confirmation_email_note'),
            'reminder_email_note' => ! Schema::hasColumn('products', 'reminder_email_note'),
        ];
        if (in_array(true, $productColumns, true)) {
            Schema::table('products', function (Blueprint $table) use ($productColumns) {
                if ($productColumns['preparation_time_minutes']) {
                    $table->unsignedSmallInteger('preparation_time_minutes')->nullable();
                }
                if ($productColumns['buffer_time_after_minutes']) {
                    $table->unsignedSmallInteger('buffer_time_after_minutes')->nullable();
                }
                if ($productColumns['confirmation_email_note']) {
                    $table->text('confirmation_email_note')->nullable();
                }
                if ($productColumns['reminder_email_note']) {
                    $table->text('reminder_email_note')->nullable();
                }
            });
        }

        $appointmentColumns = [
            'preparation_time_minutes' => ! Schema::hasColumn('appointments', 'preparation_time_minutes'),
            'buffer_time_after_minutes' => ! Schema::hasColumn('appointments', 'buffer_time_after_minutes'),
            'confirmation_email_note' => ! Schema::hasColumn('appointments', 'confirmation_email_note'),
            'reminder_email_note' => ! Schema::hasColumn('appointments', 'reminder_email_note'),
        ];
        if (in_array(true, $appointmentColumns, true)) {
            Schema::table('appointments', function (Blueprint $table) use ($appointmentColumns) {
                if ($appointmentColumns['preparation_time_minutes']) {
                    $table->unsignedSmallInteger('preparation_time_minutes')->nullable();
                }
                if ($appointmentColumns['buffer_time_after_minutes']) {
                    $table->unsignedSmallInteger('buffer_time_after_minutes')->nullable();
                }
                if ($appointmentColumns['confirmation_email_note']) {
                    $table->text('confirmation_email_note')->nullable();
                }
                if ($appointmentColumns['reminder_email_note']) {
                    $table->text('reminder_email_note')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $appointmentColumns = array_values(array_filter([
            'preparation_time_minutes',
            'buffer_time_after_minutes',
            'confirmation_email_note',
            'reminder_email_note',
        ], fn (string $column): bool => Schema::hasColumn('appointments', $column)));
        if ($appointmentColumns !== []) {
            Schema::table('appointments', fn (Blueprint $table) => $table->dropColumn($appointmentColumns));
        }

        $productColumns = array_values(array_filter([
            'preparation_time_minutes',
            'buffer_time_after_minutes',
            'confirmation_email_note',
            'reminder_email_note',
        ], fn (string $column): bool => Schema::hasColumn('products', $column)));
        if ($productColumns !== []) {
            Schema::table('products', fn (Blueprint $table) => $table->dropColumn($productColumns));
        }

        $userColumns = array_values(array_filter([
            'booking_schedule_mode',
            'booking_slot_interval_minutes',
            'information_requests_enabled',
        ], fn (string $column): bool => Schema::hasColumn('users', $column)));
        if ($userColumns !== []) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn($userColumns));
        }
    }
};
