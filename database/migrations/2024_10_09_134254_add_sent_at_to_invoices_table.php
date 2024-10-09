<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_xxxxxx_add_sent_at_to_invoices_table.php

public function up()
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->timestamp('sent_at')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->dropColumn('sent_at');
    });
}

};
