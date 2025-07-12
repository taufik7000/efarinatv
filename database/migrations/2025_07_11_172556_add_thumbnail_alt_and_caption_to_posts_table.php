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
        Schema::table('posts', function (Blueprint $table) {
            // Menambahkan kolom untuk alt text dan caption gambar thumbnail
            $table->string('thumbnail_alt')->nullable()->after('thumbnail');
            $table->string('thumbnail_caption')->nullable()->after('thumbnail_alt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn('thumbnail_caption');
            $table->dropColumn('thumbnail_alt');
        });
    }
};