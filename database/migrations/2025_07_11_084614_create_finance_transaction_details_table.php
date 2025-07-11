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
        Schema::create('finance_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->comment('ID transaksi induk')->constrained('finance_transactions')->onDelete('cascade');
            $table->foreignId('team_id')->comment('Tim yang dibebankan biaya')->constrained('teams')->onDelete('cascade');
            $table->foreignId('category_id')->comment('Kategori biaya')->constrained('finance_categories')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->comment('Jumlah yang dibebankan untuk rincian ini');
            $table->text('description')->nullable()->comment('Catatan spesifik untuk rincian ini');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_transaction_details');
    }
};