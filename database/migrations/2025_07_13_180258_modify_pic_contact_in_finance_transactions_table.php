<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            // Hapus kolom string yang lama
            $table->dropColumn('pic_contact');
            // Tambahkan kolom foreign key baru yang bisa null
            $table->foreignId('pic_user_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            // Logika untuk mengembalikan perubahan
            $table->dropForeign(['pic_user_id']);
            $table->dropColumn('pic_user_id');
            $table->string('pic_contact')->nullable();
        });
    }
};