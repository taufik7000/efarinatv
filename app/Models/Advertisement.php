<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advertisement extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Memberitahu Laravel untuk secara otomatis mengubah kolom ini
     * menjadi objek Carbon (tanggal dan waktu).
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function adType(): BelongsTo
    {
        return $this->belongsTo(AdType::class);
    }

    public function marketingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketing_user_id');
    }

    
}
