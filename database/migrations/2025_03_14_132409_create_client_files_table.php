<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->constrained()->onDelete('cascade');
            $table->string('file_path'); // path to the file
            $table->string('original_name'); // original filename
            $table->string('mime_type')->nullable(); // mime type if needed
            $table->bigInteger('size')->nullable(); // file size in bytes
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_files');
    }
};
