<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceCategory extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'finance_categories';

    /**
     * Atribut yang tidak boleh diisi secara massal.
     *
     * @var array
     */
    protected $guarded = ['id'];
    public function budgetType(): BelongsTo
    {
        return $this->belongsTo(BudgetType::class);
    }
}