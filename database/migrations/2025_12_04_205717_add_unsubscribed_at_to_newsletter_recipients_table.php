<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('newsletter_recipients', function (Blueprint $table) {
            if (!Schema::hasColumn('newsletter_recipients', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_recipients', function (Blueprint $table) {
            if (Schema::hasColumn('newsletter_recipients', 'unsubscribed_at')) {
                $table->dropColumn('unsubscribed_at');
            }
        });
    }
};
