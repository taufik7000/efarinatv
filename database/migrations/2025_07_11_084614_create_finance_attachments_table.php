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
        Schema::create('finance_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->comment('ID transaksi tempat lampiran ini terhubung')->constrained('finance_transactions')->onDelete('cascade');
            $table->string('file_path')->comment('Lokasi penyimpanan file di server');
            $table->string('file_name')->comment('Nama asli file yang diunggah');
            $table->string('file_type')->comment('Tipe MIME dari file');
            $table->unsignedInteger('file_size')->comment('Ukuran file dalam bytes');
            $table->foreignId('uploaded_by')->comment('ID user yang mengunggah file')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_attachments');
    }
};