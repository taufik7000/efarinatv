<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    /**
     * Atribut yang tidak boleh diisi secara massal.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Mendefinisikan relasi "one-to-many" ke model FinanceBudget.
     * Sebuah tim bisa memiliki banyak alokasi anggaran.
     */
    public function financeBudgets(): HasMany
    {
        return $this->hasMany(FinanceBudget::class);
    }

    /**
     * Mendefinisikan relasi "one-to-many" ke model FinanceTransactionDetail.
     * Sebuah tim bisa memiliki banyak rincian transaksi.
     */
    public function financeTransactionDetails(): HasMany
    {
        return $this->hasMany(FinanceTransactionDetail::class);
    }
}