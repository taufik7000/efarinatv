<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    use HasFactory;
    protected $table = 'post_categories';
    protected $guarded = ['id'];

    /**
     * Relasi ke posts.
     * Satu kategori bisa memiliki banyak post.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'category_id');
    }
}