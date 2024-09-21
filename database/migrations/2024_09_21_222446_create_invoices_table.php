<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_profile_id'); // Client profile for the invoice
            $table->unsignedBigInteger('user_id'); // Therapist who issued the invoice
            $table->date('invoice_date');
            $table->decimal('total_amount', 10, 2); // Total invoice amount
            $table->string('status')->default('unpaid'); // unpaid, paid, etc.
            $table->timestamps();

            // Foreign key relations
            $table->foreign('client_profile_id')->references('id')->on('client_profiles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
