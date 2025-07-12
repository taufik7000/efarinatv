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
     * Default values untuk attributes
     */
    protected $attributes = [
        'color' => '#6b7280'
    ];

    /**
     * Relasi ke tasks
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'category_id');
    }

    /**
     * Accessor untuk mendapatkan nama dengan warna
     */
    public function getNameWithColorAttribute(): string
    {
        return $this->name;
    }

    /**
     * Scope untuk kategori yang aktif digunakan
     */
    public function scopeWithTaskCount($query)
    {
        return $query->withCount('tasks');
    }
}