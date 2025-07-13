<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->foreignId('source_accountability_report_id')->nullable()->after('advertisement_id')->constrained('accountability_reports')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropForeign(['source_accountability_report_id']);
            $table->dropColumn('source_accountability_report_id');
        });
    }
};