<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman utama (homepage).
     */
    public function index()
    {
        // Ambil semua berita yang sudah publish dan urutkan dari yang terbaru
        $latestPosts = Post::where('status', 'published')
                           ->where('published_at', '<=', now())
                           ->with(['author', 'category'])
                           ->latest('published_at')
                           ->take(5) // Ambil 5 berita terbaru
                           ->get();

        // Pisahkan berita pertama sebagai berita utama (hero)
        $heroPost = $latestPosts->shift();

        // Sisa 4 berita akan menjadi berita terbaru
        $recentPosts = $latestPosts;

        // Kirim data ke view
        return view('home', compact('heroPost', 'recentPosts'));
    }
}