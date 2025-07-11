<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nama jenis iklan, cth: TVC 30 Detik');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_types');
    }
};
