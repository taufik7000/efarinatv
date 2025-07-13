<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('accountability_reports', function (Blueprint $table) {
            $table->string('return_status')->nullable()->after('status')->comment('Status pengembalian dana sisa');
            $table->string('return_proof_path')->nullable()->after('return_status');
            $table->text('return_notes')->nullable()->after('return_proof_path');
        });
    }
    public function down(): void {
        Schema::table('accountability_reports', function (Blueprint $table) {
            $table->dropColumn(['return_status', 'return_proof_path', 'return_notes']);
        });
    }
};