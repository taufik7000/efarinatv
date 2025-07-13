<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->comment('User yang memproses pembayaran');
            $table->date('payment_date');
            $table->string('payment_method'); // e.g., 'transfer', 'cash'
            $table->string('reference_number')->nullable();
            $table->string('proof_path'); // Path ke file bukti bayar
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};