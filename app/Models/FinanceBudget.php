<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceBudget extends Model
{
    use HasFactory;
    protected $table = 'finance_budgets';
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // --- AWAL PERUBAHAN ---
    /**
     * Mengganti relasi dari category() menjadi budgetType().
     */
    public function budgetType(): BelongsTo
    {
        return $this->belongsTo(BudgetType::class);
    }

    /**
     * Accessor untuk menghitung total dana yang terpakai untuk budget ini.
     * Ini adalah inti dari logika pelaporan Anda.
     */
    public function getUsedAmountAttribute(): float
    {
        // 1. Dapatkan semua ID kategori yang berada di bawah jenis budget ini.
        $categoryIds = FinanceCategory::where('budget_type_id', $this->budget_type_id)->pluck('id');

        // 2. Cari semua detail transaksi yang menggunakan kategori-kategori tersebut.
        $transactionDetails = FinanceTransactionDetail::whereIn('category_id', $categoryIds)->get();

        // 3. Dapatkan ID transaksi unik dari detail tersebut.
        $transactionIds = $transactionDetails->pluck('transaction_id')->unique();

        // 4. Hitung total dari transaksi yang sesuai dengan tahun dan status.
        return FinanceTransaction::whereIn('id', $transactionIds)
            ->whereYear('transaction_date', $this->year)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('total_amount');
    }
    // --- AKHIR PERUBAHAN ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}