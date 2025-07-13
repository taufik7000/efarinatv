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
        Schema::table('finance_budgets', function (Blueprint $table) {
            // Cek dulu apakah kolom team_id masih ada sebelum melakukan apapun
            if (Schema::hasColumn('finance_budgets', 'team_id')) {
                // Hapus foreign key terlebih dahulu
                $table->dropForeign(['team_id']);
                // Kemudian hapus kolomnya
                $table->dropColumn('team_id');
            }

            if (Schema::hasColumn('finance_budgets', 'start_date')) {
                $table->dropColumn('start_date');
            }

            if (Schema::hasColumn('finance_budgets', 'end_date')) {
                $table->dropColumn('end_date');
            }

            // Tambahkan kolom baru (jika belum ada)
            if (!Schema::hasColumn('finance_budgets', 'period_type')) {
                $table->string('period_type')->default('monthly')->after('amount');
            }
            if (!Schema::hasColumn('finance_budgets', 'year')) {
                $table->year('year')->after('period_type');
            }
            if (!Schema::hasColumn('finance_budgets', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('year');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Logika untuk rollback tidak perlu diubah, sudah aman.
        Schema::table('finance_budgets', function (Blueprint $table) {
            $table->dropColumn(['period_type', 'year', 'is_active']);
            
            $table->unsignedBigInteger('team_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });
    }
};