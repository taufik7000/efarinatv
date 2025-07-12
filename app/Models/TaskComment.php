<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskComment extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    /**
     * Relasi ke task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Relasi ke user yang comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}