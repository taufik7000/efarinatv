@extends('layouts.app')

@section('title', 'Efarina TV - Berita Terkini dan Terpercaya')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

    @if($heroPost)
        <section class="mb-12 group">
            <a href="{{ route('posts.show', $heroPost->slug) }}" class="block">
                <div class="relative w-full h-[30rem] rounded-xl overflow-hidden shadow-2xl">
                    <img src="{{ asset('storage/' . $heroPost->thumbnail) }}" alt="{{ $heroPost->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                    
                    <div class="absolute bottom-0 left-0 p-6 md:p-10 text-white w-full md:w-3/4 lg:w-2/3">
                        <span class="bg-blue-600 text-white px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full">{{ $heroPost->category->name }}</span>
                        <h1 class="mt-4 text-3xl md:text-5xl font-extrabold leading-tight shadow-text group-hover:underline">
                            {{ $heroPost->title }}
                        </h1>
                        <p class="mt-3 text-sm md:text-base opacity-90">
                            Oleh {{ $heroPost->author->name }} &bull; {{ $heroPost->published_at->format('d F Y') }}
                        </p>
                    </div>
                </div>
            </a>
        </section>
    @endif

    <section>
        <h2 class="text-3xl font-bold text-gray-900 mb-8 border-l-4 border-blue-600 pl-4">Berita Terbaru</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-10">
            @forelse($recentPosts as $post)
                <article class="flex flex-col bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
                    <a href="{{ route('posts.show', $post->slug) }}">
                        @if($post->thumbnail)
                            <img src="{{ asset('storage/' . $post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-44 object-cover">
                        @else
                            <img src="https://via.placeholder.com/400x200.png?text=Efarina+TV" alt="Default Image" class="w-full h-44 object-cover">
                        @endif
                    </a>
                    <div class="p-5 flex flex-col flex-grow">
                        <div class="flex-grow">
                            <span class="text-xs font-bold uppercase text-blue-600">{{ $post->category->name }}</span>
                            <h3 class="mt-2 text-lg font-bold leading-snug text-gray-900">
                                <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-blue-700">{{ $post->title }}</a>
                            </h3>
                        </div>
                        <p class="text-gray-500 text-xs mt-4">
                            {{ $post->published_at->format('d F Y') }}
                        </p>
                    </div>
                </article>
            @empty
                <p class="col-span-full text-center text-gray-500 py-10">Belum ada berita terbaru untuk ditampilkan.</p>
            @endforelse
        </div>
    </section>

    @if($recentPosts->count() > 0)
    <div class="text-center mt-12">
        <a href="{{ route('posts.index') }}" class="inline-block bg-gray-900 text-white font-semibold px-8 py-3 rounded-lg hover:bg-gray-700 transition-all duration-300 transform hover:scale-105">
            Lihat Semua Berita &rarr;
        </a>
    </div>
    @endif
</div>

<style>
    .shadow-text {
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
    }
</style>
@endsection