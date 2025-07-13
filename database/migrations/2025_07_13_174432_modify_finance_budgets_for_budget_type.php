<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_budgets', function (Blueprint $table) {
            // Hapus foreign key dan kolom category_id yang lama
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            // Tambahkan foreign key baru ke tabel budget_types
            $table->foreignId('budget_type_id')->after('id')->constrained('budget_types');
        });
    }

    public function down(): void
    {
        Schema::table('finance_budgets', function (Blueprint $table) {
            // Logika untuk mengembalikan perubahan
            $table->dropForeign(['budget_type_id']);
            $table->dropColumn('budget_type_id');
            $table->foreignId('category_id')->constrained('finance_categories');
        });
    }
};