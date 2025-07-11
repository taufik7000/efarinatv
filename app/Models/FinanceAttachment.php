<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceAttachment extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'finance_attachments';

    /**
     * Atribut yang tidak boleh diisi secara massal untuk melindungi dari mass assignment.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Mendefinisikan relasi "belongsTo" ke model FinanceTransaction.
     * Setiap lampiran dimiliki oleh satu transaksi induk.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'transaction_id');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model User.
     * Menunjukkan siapa yang mengunggah file ini.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
