<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_categories', function (Blueprint $table) {
            // Tambahkan foreign key ke tabel budget_types
            $table->foreignId('budget_type_id')->nullable()->after('description')->constrained('budget_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_categories', function (Blueprint $table) {
            // Logika untuk rollback
            $table->dropForeign(['budget_type_id']);
            $table->dropColumn('budget_type_id');
        });
    }
};