<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceTransactionDetail extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'finance_transaction_details';

    /**
     * Atribut yang tidak boleh diisi secara massal untuk melindungi dari mass assignment.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Mendefinisikan relasi "belongsTo" ke model FinanceTransaction.
     * Setiap rincian transaksi dimiliki oleh satu transaksi induk.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'transaction_id');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Team.
     * Setiap rincian transaksi dibebankan ke satu tim.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model FinanceCategory.
     * Setiap rincian transaksi masuk ke dalam satu kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }
}