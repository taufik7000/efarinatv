<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            // Tambahkan kolom foreign key baru, boleh null untuk transisi
            $table->foreignId('budget_type_id')->nullable()->after('description')->constrained('budget_types')->nullOnDelete();
            // Hapus kolom string yang lama
            $table->dropColumn('budget_type');
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            // Logika untuk mengembalikan perubahan jika rollback
            $table->dropForeign(['budget_type_id']);
            $table->dropColumn('budget_type_id');
            $table->string('budget_type')->nullable()->after('description');
        });
    }
};