<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accountability_report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accountability_report_id')->constrained()->cascadeOnDelete();
            
            // --- AWAL PERBAIKAN ---
            // Menggunakan nama constraint kustom yang lebih pendek
            $table->foreignId('finance_transaction_detail_id')
                  ->constrained()
                  ->cascadeOnDelete()
                  ->name('fk_acc_report_details_to_fin_trans_details'); // Nama kustom
            // --- AKHIR PERBAIKAN ---

            $table->decimal('actual_amount', 15, 2);
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accountability_report_details');
    }
};