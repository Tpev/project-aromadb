<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')
            ->where('visio_provider', 'aromamade')
            ->update(['visio_provider' => 'olithea']);
    }

    public function down(): void
    {
        DB::table('events')
            ->where('visio_provider', 'olithea')
            ->update(['visio_provider' => 'aromamade']);
    }
};
