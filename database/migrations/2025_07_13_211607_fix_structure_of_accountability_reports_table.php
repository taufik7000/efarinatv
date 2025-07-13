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
        Schema::table('accountability_reports', function (Blueprint $table) {
            // Cek dan hapus kolom 'actual_amount_spent' jika ada
            if (Schema::hasColumn('accountability_reports', 'actual_amount_spent')) {
                $table->dropColumn('actual_amount_spent');
            }

            // Cek dan hapus kolom 'receipts' jika ada
            if (Schema::hasColumn('accountability_reports', 'receipts')) {
                $table->dropColumn('receipts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Logika untuk mengembalikan kolom jika diperlukan (rollback)
        Schema::table('accountability_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('accountability_reports', 'actual_amount_spent')) {
                $table->decimal('actual_amount_spent', 15, 2);
            }
            if (!Schema::hasColumn('accountability_reports', 'receipts')) {
                $table->json('receipts')->nullable();
            }
        });
    }
};