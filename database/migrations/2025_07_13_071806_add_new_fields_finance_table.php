<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tambah kolom baru ke finance_transactions
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->string('project_name')->nullable()->after('description');
            $table->enum('urgency_level', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('status');
            $table->enum('budget_type', ['operational', 'project', 'equipment', 'travel', 'event', 'emergency', 'training', 'maintenance'])->nullable()->after('urgency_level');
            $table->string('expected_completion')->nullable()->after('budget_type');
            $table->string('approval_needed_by')->nullable()->after('expected_completion');
            $table->string('pic_contact')->nullable()->after('approval_needed_by');
            $table->text('additional_notes')->nullable()->after('pic_contact');
            $table->boolean('is_urgent_request')->default(false)->after('additional_notes');
            $table->text('urgent_reason')->nullable()->after('is_urgent_request');
        });

        // Tambah kolom baru ke finance_transaction_details
        Schema::table('finance_transaction_details', function (Blueprint $table) {
            $table->string('item_description')->nullable()->after('amount');
            $table->integer('quantity')->default(1)->after('item_description');
            $table->decimal('unit_price', 15, 2)->nullable()->after('quantity');
            $table->text('justification')->nullable()->after('unit_price');
            $table->string('supplier_vendor')->nullable()->after('justification');
        });

        // Tambah kolom baru ke finance_attachments
        Schema::table('finance_attachments', function (Blueprint $table) {
            $table->enum('document_type', ['quotation', 'specification', 'comparison', 'proposal', 'reference', 'other'])->default('other')->after('file_path');
            $table->string('document_description')->nullable()->after('document_type');
        });
    }

    public function down()
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'project_name', 'urgency_level', 'budget_type', 'expected_completion',
                'approval_needed_by', 'pic_contact', 'additional_notes', 
                'is_urgent_request', 'urgent_reason'
            ]);
        });

        Schema::table('finance_transaction_details', function (Blueprint $table) {
            $table->dropColumn([
                'item_description', 'quantity', 'unit_price', 'justification', 'supplier_vendor'
            ]);
        });

        Schema::table('finance_attachments', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'document_description']);
        });
    }
};