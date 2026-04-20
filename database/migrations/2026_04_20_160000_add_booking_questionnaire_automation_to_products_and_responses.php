<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'booking_questionnaire_enabled')) {
                $table->boolean('booking_questionnaire_enabled')->default(false)->after('price_visible_in_portal');
            }

            if (!Schema::hasColumn('products', 'booking_questionnaire_id')) {
                $table->unsignedBigInteger('booking_questionnaire_id')->nullable()->after('booking_questionnaire_enabled');
            }

            if (!Schema::hasColumn('products', 'booking_questionnaire_frequency')) {
                $table->string('booking_questionnaire_frequency', 30)
                    ->nullable()
                    ->after('booking_questionnaire_id');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $foreignKeys = collect(Schema::getForeignKeys('products'))->pluck('name')->all();

            if (
                Schema::hasColumn('products', 'booking_questionnaire_id')
                && !in_array('products_booking_questionnaire_fk', $foreignKeys, true)
            ) {
                $table->foreign('booking_questionnaire_id', 'products_booking_questionnaire_fk')
                    ->references('id')
                    ->on('questionnaires')
                    ->nullOnDelete();
            }
        });

        Schema::table('responses', function (Blueprint $table) {
            if (!Schema::hasColumn('responses', 'appointment_id')) {
                $table->unsignedBigInteger('appointment_id')->nullable()->after('client_profile_id');
            }

            if (!Schema::hasColumn('responses', 'source')) {
                $table->string('source', 40)->nullable()->after('is_completed');
            }
        });

        Schema::table('responses', function (Blueprint $table) {
            $foreignKeys = collect(Schema::getForeignKeys('responses'))->pluck('name')->all();
            $indexes = collect(Schema::getIndexes('responses'))->pluck('name')->all();

            if (
                Schema::hasColumn('responses', 'appointment_id')
                && !in_array('responses_appointment_fk', $foreignKeys, true)
            ) {
                $table->foreign('appointment_id', 'responses_appointment_fk')
                    ->references('id')
                    ->on('appointments')
                    ->nullOnDelete();
            }

            if (!in_array('responses_appointment_source_idx', $indexes, true)) {
                $table->index(['appointment_id', 'source'], 'responses_appointment_source_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $foreignKeys = collect(Schema::getForeignKeys('responses'))->pluck('name')->all();
            $indexes = collect(Schema::getIndexes('responses'))->pluck('name')->all();

            if (in_array('responses_appointment_fk', $foreignKeys, true)) {
                $table->dropForeign('responses_appointment_fk');
            }

            if (in_array('responses_appointment_source_idx', $indexes, true)) {
                $table->dropIndex('responses_appointment_source_idx');
            }

            if (Schema::hasColumn('responses', 'appointment_id')) {
                $table->dropColumn('appointment_id');
            }

            if (Schema::hasColumn('responses', 'source')) {
                $table->dropColumn('source');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $foreignKeys = collect(Schema::getForeignKeys('products'))->pluck('name')->all();

            if (in_array('products_booking_questionnaire_fk', $foreignKeys, true)) {
                $table->dropForeign('products_booking_questionnaire_fk');
            }

            foreach (['booking_questionnaire_frequency', 'booking_questionnaire_id', 'booking_questionnaire_enabled'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
