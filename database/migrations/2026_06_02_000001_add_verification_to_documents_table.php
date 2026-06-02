<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('category')->default('umum')->after('status'); // umum, bukti_bayar
            $table->string('verification_status')->default('none')->after('category'); // none, pending, approved, rejected
            $table->integer('payment_month')->nullable()->after('verification_status'); // 0-11
            $table->integer('payment_year')->nullable()->after('payment_month');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['category', 'verification_status', 'payment_month', 'payment_year']);
        });
    }
};
