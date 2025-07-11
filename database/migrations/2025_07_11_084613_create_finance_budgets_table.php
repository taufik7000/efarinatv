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
        Schema::create('finance_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->comment('Anggaran untuk tim spesifik, null jika berlaku umum')->constrained()->onDelete('set null');
            $table->foreignId('category_id')->comment('Anggaran untuk kategori spesifik')->constrained('finance_categories')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->comment('Jumlah total anggaran yang dialokasikan');
            $table->date('start_date')->comment('Tanggal mulai periode anggaran');
            $table->date('end_date')->comment('Tanggal berakhir periode anggaran');
            $table->foreignId('user_id')->comment('ID user (Direktur) yang membuat anggaran')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_budgets');
    }
};