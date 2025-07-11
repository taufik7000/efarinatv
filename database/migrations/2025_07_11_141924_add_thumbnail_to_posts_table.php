<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     * Ini akan menambahkan kolom baru.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Menambahkan kolom 'thumbnail' setelah kolom 'slug'
            $table->string('thumbnail')->nullable()->after('slug');
        });
    }

    /**
     * Batalkan migrasi.
     * Ini akan menghapus kolom jika migrasi di-rollback.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('thumbnail');
        });
    }
};