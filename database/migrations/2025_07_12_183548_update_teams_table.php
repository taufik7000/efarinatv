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
        // Update tabel teams untuk menambah field baru
        Schema::table('teams', function (Blueprint $table) {
            $table->string('department')->nullable()->after('description');
            $table->foreignId('team_leader_id')->nullable()->constrained('users')->onDelete('set null')->after('department');
            $table->boolean('is_active')->default(true)->after('team_leader_id');
        });

        // Buat tabel pivot untuk relasi many-to-many team dengan user
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplikasi
            $table->unique(['team_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
        
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['team_leader_id']);
            $table->dropColumn(['department', 'team_leader_id', 'is_active']);
        });
    }
};