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
        // Debug: Cek total posts yang ada
        $totalPosts = Post::count();
        
        // Debug: Cek posts dengan status published
        $publishedPosts = Post::where('status', 'published')->count();
        
        // Debug: Cek posts dengan tanggal published <= sekarang
        $currentPublishedPosts = Post::where('status', 'published')
                                   ->where('published_at', '<=', now())
                                   ->count();

        // Ambil semua berita yang sudah publish untuk home page
        $recentPosts = Post::where('status', 'published')
                          ->where('published_at', '<=', now())
                          ->with(['author', 'category'])
                          ->latest('published_at')
                          ->take(15) // Ambil 15 berita untuk semua keperluan
                          ->get();

        // Jika tidak ada posts dengan status published, ambil semua posts untuk development
        if ($recentPosts->isEmpty()) {
            $recentPosts = Post::with(['author', 'category'])
                              ->latest('created_at')
                              ->take(15)
                              ->get();
        }

        // Debug info (bisa dihapus di production)
        if (app()->environment('local')) {
            \Log::info('Home Controller Debug:', [
                'total_posts' => $totalPosts,
                'published_posts' => $publishedPosts,
                'current_published_posts' => $currentPublishedPosts,
                'recent_posts_count' => $recentPosts->count(),
                'recent_posts_titles' => $recentPosts->pluck('title')->toArray()
            ]);
        }

        // Kirim data ke view
        return view('home', compact('recentPosts'));
    }
}