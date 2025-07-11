<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PostTag extends Model
{
    use HasFactory;
    protected $table = 'post_tags';
    protected $guarded = ['id'];

    /**
     * Relasi ke posts.
     * Satu tag bisa dimiliki oleh banyak post.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag', 'tag_id', 'post_id');
    }
}