<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_training_pack_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_product_id')->constrained('pack_products')->cascadeOnDelete();
            $table->foreignId('digital_training_id')->constrained('digital_trainings')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['pack_product_id', 'digital_training_id'],
                'dt_pack_product_unique'
            );
        });

        Schema::table('digital_training_enrollments', function (Blueprint $table) {
            $table->foreignId('pack_purchase_id')
                ->nullable()
                ->after('digital_training_id')
                ->constrained('pack_purchases')
                ->cascadeOnDelete();
            $table->timestamp('access_email_sent_at')->nullable()->after('token_expires_at');

            $table->unique(
                ['pack_purchase_id', 'digital_training_id'],
                'dt_enrollment_pack_training_unique'
            );
        });

        Schema::table('pack_purchases', function (Blueprint $table) {
            $table->json('digital_training_ids_snapshot')->nullable()->after('digital_training_id');
        });

        // All purchases created before this optional feature keep their exact legacy behaviour.
        // A non-null empty snapshot prevents a later pack edit or Stripe renewal from granting
        // newly configured training access retroactively.
        DB::table('pack_purchases')
            ->whereNull('digital_training_ids_snapshot')
            ->update(['digital_training_ids_snapshot' => json_encode([])]);
    }

    public function down(): void
    {
        Schema::table('pack_purchases', function (Blueprint $table) {
            $table->dropColumn('digital_training_ids_snapshot');
        });

        Schema::table('digital_training_enrollments', function (Blueprint $table) {
            $table->dropUnique('dt_enrollment_pack_training_unique');
            $table->dropForeign(['pack_purchase_id']);
            $table->dropColumn(['pack_purchase_id', 'access_email_sent_at']);
        });

        Schema::dropIfExists('digital_training_pack_product');
    }
};
