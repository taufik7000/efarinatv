<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskCategory extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    /**
     * Relasi ke tasks
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'category_id');
    }
}