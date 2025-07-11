<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            // Kolom ini akan menghubungkan transaksi langsung ke iklannya
            $table->foreignId('advertisement_id')->nullable()->after('id')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropForeign(['advertisement_id']);
            $table->dropColumn('advertisement_id');
        });
    }
};
