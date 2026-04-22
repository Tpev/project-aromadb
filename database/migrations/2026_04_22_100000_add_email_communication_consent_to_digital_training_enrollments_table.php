<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('digital_training_enrollments')) {
            return;
        }

        Schema::table('digital_training_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('digital_training_enrollments', 'email_communication_consent')) {
                $table->boolean('email_communication_consent')
                    ->default(false)
                    ->after('source');
            }

            if (! Schema::hasColumn('digital_training_enrollments', 'email_communication_consent_at')) {
                $table->timestamp('email_communication_consent_at')
                    ->nullable()
                    ->after('email_communication_consent');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('digital_training_enrollments')) {
            return;
        }

        Schema::table('digital_training_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('digital_training_enrollments', 'email_communication_consent_at')) {
                $table->dropColumn('email_communication_consent_at');
            }

            if (Schema::hasColumn('digital_training_enrollments', 'email_communication_consent')) {
                $table->dropColumn('email_communication_consent');
            }
        });
    }
};
