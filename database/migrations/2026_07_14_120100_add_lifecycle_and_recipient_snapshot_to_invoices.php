<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('finalized_at')->nullable()->after('sent_at');
            $table->json('recipient_snapshot')->nullable()->after('finalized_at');
            $table->foreignId('original_invoice_id')
                ->nullable()
                ->after('appointment_id')
                ->constrained('invoices')
                ->nullOnDelete();
            $table->string('correction_kind', 32)->nullable()->after('original_invoice_id');
            $table->text('correction_reason')->nullable()->after('correction_kind');

            $table->index(['user_id', 'original_invoice_id'], 'invoices_user_original_idx');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_user_original_idx');
            $table->dropConstrainedForeignId('original_invoice_id');
            $table->dropColumn([
                'finalized_at',
                'recipient_snapshot',
                'correction_kind',
                'correction_reason',
            ]);
        });
    }
};
