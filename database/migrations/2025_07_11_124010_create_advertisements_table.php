<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('ad_code')->unique()->comment('Kode unik untuk setiap iklan');
            $table->string('client_name')->comment('Nama klien atau brand');
            $table->string('title')->comment('Judul kampanye iklan');
            $table->foreignId('ad_type_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 15, 2)->comment('Biaya iklan yang akan menjadi pemasukan');
            $table->dateTime('start_date')->comment('Tanggal mulai tayang');
            $table->dateTime('end_date')->comment('Tanggal selesai tayang');
            $table->string('status')->default('active')->comment('Status iklan: active, completed, cancelled');
            $table->foreignId('marketing_user_id')->comment('User marketing yang menjual iklan ini')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
