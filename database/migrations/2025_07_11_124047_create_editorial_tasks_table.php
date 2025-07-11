<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editorial_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->comment('Tugas ini terkait dengan iklan mana')->constrained()->onDelete('cascade');
            $table->text('description')->comment('Deskripsi tugas untuk tim redaksi');
            $table->dateTime('due_date')->comment('Batas waktu pengerjaan tugas');
            $table->string('status')->default('pending')->comment('Status tugas: pending, in_progress, completed');
            $table->foreignId('assigned_to_user_id')->nullable()->comment('User redaksi yang ditugaskan')->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editorial_tasks');
    }
};
