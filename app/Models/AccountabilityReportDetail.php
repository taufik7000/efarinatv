<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountabilityReportDetail extends Model
{
    use HasFactory;

    protected $table = 'accountability_report_details';

    protected $guarded = ['id'];

    /**
     * Relasi ke laporan pertanggungjawaban induk.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(AccountabilityReport::class, 'accountability_report_id');
    }

    /**
     * Relasi ke rincian transaksi asli yang diajukan.
     * Ini penting untuk menampilkan deskripsi item yang diajukan.
     */
    public function transactionDetail(): BelongsTo
    {
        return $this->belongsTo(FinanceTransactionDetail::class, 'finance_transaction_detail_id');
    }
}