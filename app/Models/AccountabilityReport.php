<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountabilityReport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function details(): HasMany
    {
        return $this->hasMany(AccountabilityReportDetail::class, 'accountability_report_id');
    }

    // --- AWAL PERBAIKAN ---
    /**
     * Accessor untuk menghitung total dana terpakai dari semua rincian.
     * Nama method ini (get...Attribute) memungkinkan kita memanggilnya
     * sebagai properti biasa: $report->actual_amount_spent
     */
    public function getActualAmountSpentAttribute(): float
    {
        return $this->details()->sum('actual_amount');
    }
    
    public function returnTransaction(): HasOne
    {
        return $this->hasOne(FinanceTransaction::class, 'source_accountability_report_id');
    }
}