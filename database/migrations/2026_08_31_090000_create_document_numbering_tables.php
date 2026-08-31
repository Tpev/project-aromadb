<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_numbering_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 16);
            $table->boolean('enabled')->default(false);
            $table->string('format', 64)->nullable();
            $table->string('reset_frequency', 16)->default('never');
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'document_type'], 'doc_numbering_settings_user_type_unique');
        });

        Schema::create('document_numbering_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 16);
            $table->unsignedInteger('version');
            $table->string('period_key', 16);
            $table->unsignedBigInteger('next_sequence')->default(1);
            $table->timestamps();

            $table->unique(
                ['user_id', 'document_type', 'version', 'period_key'],
                'doc_numbering_counter_scope_unique'
            );
        });

        Schema::create('document_numbering_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 16);
            $table->json('before_configuration')->nullable();
            $table->json('after_configuration');
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('custom_number', 128)->nullable()->after('quote_number');
            $table->string('numbering_family', 16)->nullable()->after('custom_number');
            $table->unsignedBigInteger('number_sequence')->nullable()->after('numbering_family');
            $table->string('number_period', 16)->nullable()->after('number_sequence');
            $table->unsignedInteger('numbering_version')->nullable()->after('number_period');

            $table->unique(
                ['user_id', 'numbering_family', 'custom_number'],
                'invoices_custom_number_scope_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_custom_number_scope_unique');
            $table->dropColumn([
                'custom_number',
                'numbering_family',
                'number_sequence',
                'number_period',
                'numbering_version',
            ]);
        });

        Schema::dropIfExists('document_numbering_change_logs');
        Schema::dropIfExists('document_numbering_counters');
        Schema::dropIfExists('document_numbering_settings');
    }
};
