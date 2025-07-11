<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Menampilkan daftar semua berita yang sudah publish.
     */
    public function index()
    {
        $posts = Post::where('status', 'published')
                     ->where('published_at', '<=', now())
                     ->with(['author', 'category']) // Eager loading untuk performa
                     ->latest('published_at')
                     ->paginate(10); // Menampilkan 10 berita per halaman

        return view('posts.index', compact('posts'));
    }

    /**
     * Menampilkan satu berita secara spesifik berdasarkan slug.
     */
    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)
                    ->where('status', 'published')
                    ->where('published_at', '<=', now())
                    ->with(['author', 'category', 'tags']) // Eager loading
                    ->firstOrFail(); // Error 404 jika tidak ditemukan

        return view('posts.show', compact('post'));
    }
}