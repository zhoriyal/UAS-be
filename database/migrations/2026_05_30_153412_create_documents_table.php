<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id'); // Supabase user ID
            $table->string('user_name');
            $table->string('user_email');
            $table->string('file_name');
            $table->string('file_type', 20); // PDF, XLSX, dll
            $table->string('file_size', 20); // 245 KB, dll
            $table->string('file_path'); // Path di storage
            $table->string('status')->default('Tersimpan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
