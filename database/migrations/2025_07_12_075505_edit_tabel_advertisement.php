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
        Schema::table('advertisements', function (Blueprint $table) {
            // Informasi klien tambahan
            $table->string('client_contact')->nullable()->after('client_name');
            $table->text('description')->nullable()->after('title');
            
            // Detail paket iklan
            $table->integer('frequency_per_day')->nullable()->after('end_date');
            $table->string('time_slots')->nullable()->after('frequency_per_day');
            
            // Brief dan instruksi
            $table->text('content_brief')->nullable()->after('time_slots');
            $table->text('target_audience')->nullable()->after('content_brief');
            $table->text('key_message')->nullable()->after('target_audience');
            $table->text('special_requirements')->nullable()->after('key_message');
            $table->json('reference_materials')->nullable()->after('special_requirements');
            
            // Pembayaran
            $table->enum('payment_method', ['cash', 'transfer', 'credit', 'other'])->nullable()->after('price');
            $table->string('payment_terms')->nullable()->after('payment_method');
            $table->date('payment_due_date')->nullable()->after('payment_terms');
            
            // Catatan internal
            $table->text('internal_notes')->nullable()->after('payment_proof_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn([
                'client_contact',
                'description',
                'frequency_per_day',
                'time_slots',
                'content_brief',
                'target_audience', 
                'key_message',
                'special_requirements',
                'reference_materials',
                'payment_method',
                'payment_terms',
                'payment_due_date',
                'internal_notes'
            ]);
        });
    }
};