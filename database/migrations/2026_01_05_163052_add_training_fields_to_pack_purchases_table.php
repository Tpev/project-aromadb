<?php

// database/migrations/xxxx_xx_xx_add_training_fields_to_pack_purchases_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pack_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('pack_purchases', 'purchase_type')) {
                $table->string('purchase_type')->default('pack')->after('pack_product_id'); // pack|training
            }
            if (!Schema::hasColumn('pack_purchases', 'digital_training_id')) {
                $table->unsignedBigInteger('digital_training_id')->nullable()->after('purchase_type');
                $table->index('digital_training_id');
            }
        });

        /**
         * IMPORTANT:
         * To reuse the same table for training purchases, pack_product_id must be nullable.
         * If you already have a foreign key, we do a safe raw ALTER (MySQL).
         *
         * If you want to keep Laravel schema changes only, you'd need doctrine/dbal.
         */
        DB::statement("ALTER TABLE pack_purchases MODIFY pack_product_id BIGINT UNSIGNED NULL");
    }

    public function down(): void
    {
        // revert nullable (only if you're sure no training purchases exist)
        // DB::statement("ALTER TABLE pack_purchases MODIFY pack_product_id BIGINT UNSIGNED NOT NULL");

        Schema::table('pack_purchases', function (Blueprint $table) {
            if (Schema::hasColumn('pack_purchases', 'digital_training_id')) {
                $table->dropIndex(['digital_training_id']);
                $table->dropColumn('digital_training_id');
            }
            if (Schema::hasColumn('pack_purchases', 'purchase_type')) {
                $table->dropColumn('purchase_type');
            }
        });
    }
};
