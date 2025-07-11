<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            // Mengubah nilai default untuk status iklan baru
            $table->string('status')->default('pending_payment')->comment('Status: pending_payment, active, completed, cancelled')->change();
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });
    }
};
