@extends('layouts.app')

@section('title', 'Berita Terbaru - Efarina TV')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mt-4">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-1 h-8 bg-blue-600"></div>
            <h1 class="text-3xl font-bold text-gray-900">Berita Terbaru</h1>
        </div>
        <p class="text-gray-600">Ikuti perkembangan terkini dari berbagai bidang berita dan informasi</p>
    </div>

    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Beranda
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Berita</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Main Content -->
        <div class="lg:col-span-3">
            <!-- Posts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($posts as $post)
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-all duration-300 group">
                        <a href="{{ route('posts.show', $post->slug) }}" class="block">
                            <!-- Image -->
                            <div class="relative h-48 overflow-hidden">
                                @if($post->thumbnail)
                                    <img src="{{ asset('storage/' . $post->thumbnail) }}" 
                                         alt="{{ $post->title }}" 
                                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                
                                <!-- Category Badge -->
                                <div class="absolute top-3 left-3">
                                    <span class="bg-blue-600 text-white px-2 py-1 text-xs font-bold uppercase rounded">
                                        {{ $post->category->name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-5">
                                <h2 class="text-lg font-bold text-gray-900 leading-tight mb-3 group-hover:text-blue-600 transition-colors">
                                    {{ $post->title }}
                                </h2>
                                
                                @if($post->excerpt)
                                    <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-3">
                                        {{ Str::limit(strip_tags($post->excerpt), 120) }}
                                    </p>
                                @endif

                                <!-- Meta Info -->
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>{{ $post->author->name }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ $post->published_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                @empty
                    <!-- Empty State -->
                    <div class="col-span-2 text-center py-16">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Berita</h3>
                        <p class="text-gray-500">Belum ada berita yang dipublikasikan saat ini.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($posts->hasPages())
                <div class="mt-12">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            @if ($posts->onFirstPage())
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md">
                                    Sebelumnya
                                </span>
                            @else
                                <a href="{{ $posts->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    Sebelumnya
                                </a>
                            @endif

                            @if ($posts->hasMorePages())
                                <a href="{{ $posts->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    Selanjutnya
                                </a>
                            @else
                                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md">
                                    Selanjutnya
                                </span>
                            @endif
                        </div>

                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center">
                            <div>
                                <p class="text-sm text-gray-700 mb-4">
                                    Menampilkan
                                    <span class="font-medium">{{ $posts->firstItem() }}</span>
                                    sampai
                                    <span class="font-medium">{{ $posts->lastItem() }}</span>
                                    dari
                                    <span class="font-medium">{{ $posts->total() }}</span>
                                    berita
                                </p>
                            </div>
                        </div>
                    </nav>
                    
                    <!-- Custom Pagination Links -->
                    <div class="flex justify-center">
                        {{ $posts->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Search Widget -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Cari Berita</h3>
                <form action="{{ route('posts.index') }}" method="GET">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Kata kunci..." 
                               class="w-full px-4 py-3 pl-10 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="submit" class="w-full mt-3 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Categories Widget -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Kategori</h3>
                <div class="space-y-2">
                    @php
                        $categories = \App\Models\PostCategory::withCount('posts')->get();
                    @endphp
                    @foreach($categories as $category)
                        <a href="{{ route('posts.index', ['category' => $category->slug]) }}" 
                           class="flex items-center justify-between py-2 px-3 rounded hover:bg-gray-50 transition-colors {{ request('category') == $category->slug ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                            <span>{{ $category->name }}</span>
                            <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $category->posts_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Popular Posts Widget -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Berita Popular</h3>
                <div class="space-y-4">
                    @php
                        $popularPosts = \App\Models\Post::where('status', 'published')
                                                      ->where('published_at', '<=', now())
                                                      ->with(['author', 'category'])
                                                      ->latest('published_at')
                                                      ->take(5)
                                                      ->get();
                    @endphp
                    @foreach($popularPosts as $index => $popularPost)
                        <article class="flex space-x-3">
                            <div class="flex-shrink-0">
                                <span class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white text-sm font-bold rounded">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('posts.show', $popularPost->slug) }}" class="block">
                                    <h4 class="text-sm font-semibold text-gray-900 leading-tight hover:text-blue-600 transition-colors">
                                        {{ Str::limit($popularPost->title, 60) }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $popularPost->published_at->format('d M Y') }}</p>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <!-- Advertisement -->
            <div class="bg-gray-100 rounded-lg p-6 text-center">
                <div class="h-64 flex items-center justify-center text-gray-500">
                    <div>
                        <svg class="w-16 h-16 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm font-medium">Space Iklan</p>
                        <p class="text-xs">300 x 250</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Line clamp utility */
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom pagination styles */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    space-x: 0.25rem;
}

.pagination a,
.pagination span {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    text-sm;
    font-medium;
    border: 1px solid #d1d5db;
    background-color: white;
    color: #374151;
    text-decoration: none;
    border-radius: 0.375rem;
    transition: all 0.2s;
}

.pagination a:hover {
    background-color: #f3f4f6;
    color: #2563eb;
}

.pagination .active span {
    background-color: #2563eb;
    color: white;
    border-color: #2563eb;
}
</style>
@endsection