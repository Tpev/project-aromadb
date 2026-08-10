<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->index('appt_cancelled_at_idx');
            $table->string('cancelled_by_type', 32)->nullable();
            $table->unsignedBigInteger('cancelled_by_id')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamp('rescheduled_at')->nullable()->index('appt_rescheduled_at_idx');
            $table->string('rescheduled_by_type', 32)->nullable();
            $table->unsignedBigInteger('rescheduled_by_id')->nullable();
            $table->timestamp('reminder_24h_queued_at')->nullable()->index('appt_reminder_24h_queue_idx');
            $table->timestamp('reminder_1h_queued_at')->nullable()->index('appt_reminder_1h_queue_idx');
            $table->timestamp('client_confirmation_sent_at')->nullable()->index('appt_confirmation_sent_idx');
            $table->unsignedBigInteger('consumed_pack_purchase_id')->nullable()->index('appt_pack_purchase_idx');
            $table->boolean('financial_follow_up_required')->default(false);
        });

        Schema::create('appointment_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('action', 50);
            $table->string('actor_type', 32);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['appointment_id', 'created_at'], 'appt_activity_history_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_activities');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appt_cancelled_at_idx');
            $table->dropIndex('appt_rescheduled_at_idx');
            $table->dropIndex('appt_reminder_24h_queue_idx');
            $table->dropIndex('appt_reminder_1h_queue_idx');
            $table->dropIndex('appt_confirmation_sent_idx');
            $table->dropIndex('appt_pack_purchase_idx');
            $table->dropColumn([
                'cancelled_at', 'cancelled_by_type', 'cancelled_by_id', 'cancellation_reason',
                'rescheduled_at', 'rescheduled_by_type', 'rescheduled_by_id',
                'reminder_24h_queued_at', 'reminder_1h_queued_at', 'client_confirmation_sent_at',
                'consumed_pack_purchase_id',
                'financial_follow_up_required',
            ]);
        });
    }
};
