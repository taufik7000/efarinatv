@extends('layouts.app')

@section('title', 'Efarina TV - Berita Terkini dan Terpercaya')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mt-4">

    <!-- Main Content Grid - 2 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column - Live Stream & Main Content -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Live Streaming Section -->
            <section>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Live Stream Header -->
                    <div class="bg-red-600 px-4 py-2 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="logo-efarina text-white" style="font-size: 1rem;">
                                <span>efarina</span><span>TV</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                <span class="text-white text-sm font-medium">LIVE STREAMING</span>
                            </div>
                        </div>
                        <button id="fullscreen-btn" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Video Player -->
                    <div class="relative bg-black">
                        <div class="aspect-video relative">
                            <video 
                                id="live-stream" 
                                class="w-full h-full object-cover"
                                poster="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1920 1080'><rect width='1920' height='1080' fill='%23000'/><g transform='translate(960,540)'><circle r='60' fill='none' stroke='%23fff' stroke-width='4'/><polygon points='0,-40 30,20 -30,20' fill='%23fff'/></g><text x='960' y='640' text-anchor='middle' fill='%23fff' font-size='32' font-family='Arial'>Efarina TV Live</text></svg>"
                                controls
                                preload="none"
                                playsinline>
                                <source src="https://live.efarinatv.net/hls/stream.m3u8" type="application/x-mpegURL">
                                <p class="text-white text-center p-8">
                                    Browser Anda tidak mendukung video streaming. 
                                    <a href="https://live.efarinatv.net/hls/stream.m3u8" class="text-blue-300 underline">Klik di sini untuk menonton</a>
                                </p>
                            </video>
                            
                            <!-- Custom Play Button Overlay -->
                            <div id="play-overlay" class="absolute inset-0 bg-black/30 flex items-center justify-center cursor-pointer">
                                <div class="play-button">
                                    <svg class="w-20 h-20 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Loading Overlay -->
                            <div id="loading-overlay" class="absolute inset-0 bg-black/80 flex items-center justify-center hidden">
                                <div class="text-center text-white">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto mb-3"></div>
                                    <p class="text-sm">Memuat siaran...</p>
                                </div>
                            </div>

                            <!-- Error Overlay -->
                            <div id="error-overlay" class="absolute inset-0 bg-black/90 flex items-center justify-center hidden">
                                <div class="text-center text-white p-6">
                                    <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <h3 class="text-lg font-semibold mb-2">Siaran Tidak Tersedia</h3>
                                    <p class="text-gray-300 text-sm mb-4">Siaran langsung sedang tidak tersedia.</p>
                                    <button id="retry-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm transition-colors">
                                        Coba Lagi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stream Info & Share -->
                    <div class="p-4 bg-gray-50">
                        <h2 class="text-lg font-bold text-gray-900 mb-2">
                            <span class="logo-efarina text-lg uppercase">
                                <span class="text-blue-600">efarina</span><span class="text-red-600">TV</span>
                            </span>
                            LIVE STREAMING
                        </h2>
                        <p class="text-gray-600 text-sm mb-3">Nonton streaming Efarina TV secara real time. Solusi untuk menonton acara-acara berita dan olahraga nomor satu di Indonesia.</p>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 text-sm text-gray-500">
                                <span>Share :</span>
                                <div class="flex space-x-2">
                                    <a href="#" class="w-8 h-8 bg-blue-600 hover:bg-blue-700 rounded flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                        </svg>
                                    </a>
                                    <a href="#" class="w-8 h-8 bg-blue-500 hover:bg-blue-600 rounded flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                                        </svg>
                                    </a>
                                    <a href="#" class="w-8 h-8 bg-orange-500 hover:bg-orange-600 rounded flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                        </svg>
                                    </a>
                                    <a href="#" class="w-8 h-8 bg-green-500 hover:bg-green-600 rounded flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.531 3.488"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                👥 <span id="viewer-count">Ribuan penonton</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Recent News Grid -->
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-4 border-l-4 border-blue-600 pl-3">BERITA TERBARU</h2>
                <div class="space-y-4">
                    @forelse($recentPosts as $post)
                        <article class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            <a href="{{ route('posts.show', $post->slug) }}" class="flex">
                                <!-- Image -->
                                <div class="flex-shrink-0 w-32 h-24 sm:w-40 sm:h-28">
                                    @if($post->thumbnail)
                                        <img src="{{ asset('storage/' . $post->thumbnail) }}" 
                                             alt="{{ $post->title }}" 
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 p-4 min-w-0">
                                    <!-- Title -->
                                    <h3 class="text-base font-bold text-gray-900 leading-tight mb-2 hover:text-blue-600 transition-colors">
                                        {{ $post->title }}
                                    </h3>
                                    
                                    <!-- Meta Info -->
                                    <div class="flex items-center space-x-3 mb-2">
                                        <span class="bg-blue-600 text-white px-2 py-1 text-xs font-bold uppercase rounded">
                                            {{ $post->category->name }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $post->published_at->format('d/m/Y - H:i') }}
                                        </span>
                                    </div>
                                    
                                    <!-- Excerpt -->
                                    <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                        {{ Str::limit(strip_tags($post->content), 120) }}
                                    </p>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="text-center text-gray-500 py-8">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Berita</h3>
                            <p class="text-gray-500">Belum ada berita yang dipublikasikan saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- Right Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Trending Section -->
            <section class="bg-white rounded-lg shadow-lg p-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">TRENDING</h3>
                <div class="space-y-4">
                    @php
                        $trendingPosts = \App\Models\Post::where('status', 'published')
                                                       ->where('published_at', '<=', now())
                                                       ->with(['author', 'category'])
                                                       ->latest('published_at')
                                                       ->take(5)
                                                       ->get();
                    @endphp
                    @forelse($trendingPosts as $index => $post)
                        <article class="flex space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-12 rounded overflow-hidden">
                                    @if($post->thumbnail)
                                        <img src="{{ asset('storage/' . $post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gray-200"></div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('posts.show', $post->slug) }}" class="block">
                                    <h4 class="text-sm font-semibold text-gray-900 leading-tight hover:text-blue-600 transition-colors">
                                        {{ Str::limit($post->title, 60) }}
                                    </h4>
                                    <div class="mt-1 flex items-center space-x-2">
                                        <span class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-xs font-medium">Trend</span>
                                        <span class="text-xs text-gray-500">{{ $post->published_at->format('d/m/Y - H:i') }}</span>
                                    </div>
                                </a>
                            </div>
                        </article>
                    @empty
                        <p class="text-gray-500 text-sm">Belum ada trending posts.</p>
                    @endforelse
                </div>
            </section>

            <!-- Recommended Section -->
            <section class="bg-white rounded-lg shadow-lg p-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">REKOMENDASI</h3>
                <div class="space-y-4">
                    @php
                        $recommendedPosts = \App\Models\Post::where('status', 'published')
                                                          ->where('published_at', '<=', now())
                                                          ->with(['author', 'category'])
                                                          ->latest('published_at')
                                                          ->skip(5)
                                                          ->take(3)
                                                          ->get();
                    @endphp
                    @forelse($recommendedPosts as $post)
                        <article>
                            <a href="{{ route('posts.show', $post->slug) }}" class="block">
                                <div class="h-32 rounded overflow-hidden mb-2">
                                    @if($post->thumbnail)
                                        <img src="{{ asset('storage/' . $post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gray-200"></div>
                                    @endif
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900 leading-tight hover:text-blue-600 transition-colors">
                                    {{ Str::limit($post->title, 70) }}
                                </h4>
                                <p class="mt-1 text-xs text-gray-500">{{ $post->published_at->format('d M Y') }}</p>
                            </a>
                        </article>
                    @empty
                        <p class="text-gray-500 text-sm">Belum ada rekomendasi.</p>
                    @endforelse
                </div>
            </section>

            <!-- Advertisement Space -->
            <section class="bg-gray-100 rounded-lg p-4 text-center">
                <div class="h-64 flex items-center justify-center text-gray-500">
                    <div>
                        <svg class="w-16 h-16 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm font-medium">Space Iklan</p>
                        <p class="text-xs">300 x 250</p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- View All Button -->
    @if($recentPosts->count() > 0)
    <div class="text-center mt-8">
        <a href="{{ route('posts.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
            Lihat Semua Berita &rarr;
        </a>
    </div>
    @endif
</div>

<!-- HLS.js untuk mendukung streaming HLS -->
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('live-stream');
    const loadingOverlay = document.getElementById('loading-overlay');
    const errorOverlay = document.getElementById('error-overlay');
    const playOverlay = document.getElementById('play-overlay');
    const retryBtn = document.getElementById('retry-btn');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    
    let hls;
    let isStreamInitialized = false;
    
    function initializeStream() {
        if (isStreamInitialized) return;
        
        loadingOverlay.classList.remove('hidden');
        errorOverlay.classList.add('hidden');
        playOverlay.classList.add('hidden');
        
        if (Hls.isSupported()) {
            hls = new Hls({
                enableWorker: true,
                lowLatencyMode: true,
                backBufferLength: 90,
                maxBufferLength: 30,
                maxMaxBufferLength: 60,
                startLevel: -1,
                autoStartLoad: true,
                capLevelToPlayerSize: true,
            });
            
            hls.loadSource('https://live.efarinatv.net/hls/stream.m3u8');
            hls.attachMedia(video);
            
            hls.on(Hls.Events.MANIFEST_PARSED, function() {
                console.log('Stream loaded successfully');
                loadingOverlay.classList.add('hidden');
                isStreamInitialized = true;
                // Tidak auto play, user harus klik play button
            });
            
            hls.on(Hls.Events.ERROR, function(event, data) {
                console.error('HLS Error:', data);
                handleStreamError();
            });
            
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = 'https://live.efarinatv.net/hls/stream.m3u8';
            video.addEventListener('loadedmetadata', function() {
                loadingOverlay.classList.add('hidden');
                isStreamInitialized = true;
            });
            video.addEventListener('error', handleStreamError);
        } else {
            handleStreamError();
        }
    }
    
    function handleStreamError() {
        loadingOverlay.classList.add('hidden');
        errorOverlay.classList.remove('hidden');
        playOverlay.classList.add('hidden');
        isStreamInitialized = false;
    }
    
    function destroyStream() {
        if (hls) {
            hls.destroy();
            hls = null;
        }
        isStreamInitialized = false;
    }
    
    // Play button click handler
    playOverlay.addEventListener('click', function() {
        if (!isStreamInitialized) {
            initializeStream();
        } else {
            video.play().then(() => {
                playOverlay.classList.add('hidden');
            }).catch(e => {
                console.log('Play failed:', e);
                handleStreamError();
            });
        }
    });
    
    // Retry button
    retryBtn.addEventListener('click', function() {
        destroyStream();
        setTimeout(initializeStream, 1000);
    });
    
    // Fullscreen functionality
    fullscreenBtn.addEventListener('click', function() {
        if (video.requestFullscreen) {
            video.requestFullscreen();
        } else if (video.webkitRequestFullscreen) {
            video.webkitRequestFullscreen();
        } else if (video.msRequestFullscreen) {
            video.msRequestFullscreen();
        }
    });
    
    // Video event handlers
    video.addEventListener('playing', function() {
        loadingOverlay.classList.add('hidden');
        playOverlay.classList.add('hidden');
    });
    
    video.addEventListener('pause', function() {
        playOverlay.classList.remove('hidden');
    });
    
    video.addEventListener('waiting', function() {
        if (!errorOverlay.classList.contains('hidden')) return;
        loadingOverlay.classList.remove('hidden');
    });
    
    video.addEventListener('canplay', function() {
        loadingOverlay.classList.add('hidden');
    });
    
    video.addEventListener('error', function() {
        handleStreamError();
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        destroyStream();
    });
});
</script>

<style>
/* Play button animation */
.play-button {
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    padding: 20px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.play-button:hover {
    background: rgba(0, 0, 0, 0.7);
    transform: scale(1.1);
}

.play-button svg {
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

/* Video container hover effect */
#play-overlay:hover .play-button {
    background: rgba(220, 38, 38, 0.8);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .play-button {
        padding: 15px;
    }
    
    .play-button svg {
        width: 60px;
        height: 60px;
    }
}
</style>
@endsection