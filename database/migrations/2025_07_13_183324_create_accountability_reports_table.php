<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('accountability_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->comment('User PIC yang membuat laporan');
            $table->date('report_date');
            $table->text('summary')->comment('Ringkasan penggunaan dana');
            $table->json('receipts')->nullable()->comment('Menyimpan path ke file struk/kuitansi');
            $table->decimal('actual_amount_spent', 15, 2);
            $table->string('status')->default('submitted'); // submitted, approved, revision_needed
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('accountability_reports');
    }
};