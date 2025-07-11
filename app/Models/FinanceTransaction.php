<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder; // Penting untuk Scopes

class FinanceTransaction extends Model
{
    use HasFactory;
    protected $table = 'finance_transactions';
    protected $guarded = ['id'];
    protected $casts = [
        'transaction_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // --- RELATIONS ---
    public function details(): HasMany { return $this->hasMany(FinanceTransactionDetail::class, 'transaction_id'); }
    public function attachments(): HasMany { return $this->hasMany(FinanceAttachment::class, 'transaction_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    // --- SCOPES (Untuk Query yang Efisien) ---
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeExpenses(Builder $query): void
    {
        $query->where('type', 'expense');
    }

    public function scopeIncomes(Builder $query): void
    {
        $query->where('type', 'income');
    }

    // --- ACCESSORS (Untuk Format Data Otomatis) ---
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'paid' => 'Telah Dibayar',
            default => 'Tidak Diketahui',
        };
    }
}