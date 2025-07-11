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
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense'])->comment('Jenis transaksi: pemasukan atau pengeluaran');
            $table->decimal('total_amount', 15, 2)->comment('Total nilai dari transaksi');
            $table->text('description')->comment('Deskripsi atau catatan umum mengenai transaksi');
            $table->dateTime('transaction_date')->comment('Tanggal dan waktu transaksi terjadi');
            $table->string('status')->default('pending')->comment('Status alur kerja: pending, approved, rejected, paid');
            $table->foreignId('user_id')->comment('ID user (Keuangan) yang menginput transaksi')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->comment('ID user (Direktur) yang menyetujui')->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->comment('Waktu ketika transaksi disetujui');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};