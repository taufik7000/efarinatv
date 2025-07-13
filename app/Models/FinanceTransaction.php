<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class FinanceTransaction extends Model
{
    use HasFactory;
    
    protected $table = 'finance_transactions';
    protected $guarded = ['id'];
    
    protected $casts = [
        'transaction_date' => 'datetime',
        'approved_at' => 'datetime',
        'is_urgent_request' => 'boolean',
        'total_amount' => 'decimal:2',
    ];

    // --- RELATIONS ---
    public function details(): HasMany 
    { 
        return $this->hasMany(FinanceTransactionDetail::class, 'transaction_id'); 
    }
    
    public function attachments(): HasMany 
    { 
        return $this->hasMany(FinanceAttachment::class, 'transaction_id'); 
    }
    
    public function user(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'user_id'); 
    }
    
    public function approver(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'approved_by'); 
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id');
    }

    // --- SCOPES (Untuk Query yang Efisien) ---
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeRejected(Builder $query): void
    {
        $query->where('status', 'rejected');
    }

    public function scopePaid(Builder $query): void
    {
        $query->where('status', 'paid');
    }

    public function scopeExpenses(Builder $query): void
    {
        $query->where('type', 'expense');
    }

    public function scopeIncomes(Builder $query): void
    {
        $query->where('type', 'income');
    }

    public function scopeUrgent(Builder $query): void
    {
        $query->where(function ($q) {
            $q->where('urgency_level', 'urgent')
              ->orWhere('is_urgent_request', true);
        });
    }

    public function scopeByUrgencyLevel(Builder $query, string $level): void
    {
        $query->where('urgency_level', $level);
    }

    public function scopeByBudgetType(Builder $query, string $type): void
    {
        $query->where('budget_type', $type);
    }

    public function scopeThisMonth(Builder $query): void
    {
        $query->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
    }

    public function scopeThisYear(Builder $query): void
    {
        $query->whereYear('created_at', now()->year);
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->where('transaction_date', '<', now())
              ->whereIn('status', ['pending', 'approved']);
    }

    public function scopeByUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
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

    public function getUrgencyTextAttribute(): string
    {
        return match ($this->urgency_level) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'urgent' => 'Sangat Urgent',
            default => 'Sedang',
        };
    }

    public function getBudgetTypeTextAttribute(): string
    {
        return match ($this->budget_type) {
            'operational' => 'Operasional Rutin',
            'project' => 'Proyek Khusus',
            'equipment' => 'Peralatan & Teknologi',
            'travel' => 'Perjalanan Dinas',
            'event' => 'Event & Kegiatan',
            'emergency' => 'Darurat/Tidak Terduga',
            'training' => 'Pelatihan & Pengembangan',
            'maintenance' => 'Pemeliharaan',
            default => 'Tidak Ditentukan',
        };
    }

    public function getApprovalNeededByTextAttribute(): string
    {
        return match ($this->approval_needed_by) {
            'manager' => 'Manager Redaksi',
            'finance_manager' => 'Manager Keuangan',
            'director' => 'Direktur',
            'board' => 'Dewan Direksi',
            default => 'Manager Keuangan',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->transaction_date < now() && 
               in_array($this->status, ['pending', 'approved']);
    }

    public function getDaysUntilNeededAttribute(): int
    {
        return $this->transaction_date->diffInDays(now(), false);
    }

    public function getDaysSinceCreatedAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->details()->count();
    }

    public function getTotalAttachmentsAttribute(): int
    {
        return $this->attachments()->count();
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    // --- MUTATORS ---
    public function setProjectNameAttribute($value): void
    {
        $this->attributes['project_name'] = ucwords(strtolower($value));
    }

    // --- METHODS ---
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['pending', 'rejected']);
    }

    public function canBeDeleted(): bool
    {
        return in_array($this->status, ['pending', 'rejected']);
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsApproved(int $approvedBy): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    public function markAsRejected(): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    public function calculateTotal(): float
    {
        return $this->details()->sum('amount');
    }

    public function recalculateTotal(): void
    {
        $total = $this->calculateTotal();
        $this->update(['total_amount' => $total]);
    }

    // --- BOOT METHOD ---
    protected static function boot()
    {
        parent::boot();

        // Auto calculate total saat details berubah
        static::saved(function ($transaction) {
            if ($transaction->type === 'expense') {
                $calculatedTotal = $transaction->calculateTotal();
                if ($calculatedTotal !== (float) $transaction->total_amount) {
                    $transaction->recalculateTotal();
                }
            }
        });
    }
}