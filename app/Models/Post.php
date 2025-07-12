<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory;

    /**
     * Atribut yang tidak boleh diisi secara massal.
     *
     * @var array
     */
    // Tambahkan 'thumbnail_alt' dan 'thumbnail_caption' di sini
    protected $guarded = ['id']; 

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Relasi ke penulis (User).
     * Sebuah post dimiliki oleh satu user.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke kategori (PostCategory).
     * Sebuah post termasuk dalam satu kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    /**
     * Relasi ke tag (PostTag).
     * Sebuah post bisa memiliki banyak tag.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PostTag::class, 'post_tag', 'post_id', 'tag_id');
    }
}
