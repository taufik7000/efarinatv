@extends('layouts.app')

@section('title', 'Berita Terbaru - Efarina TV')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-center text-gray-800 mb-10">Berita Efarina TV</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($posts as $post)
            <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-2 transition-transform duration-300">
                <a href="{{ route('posts.show', $post->slug) }}">
                    @if($post->thumbnail)
                        <img src="{{ asset('storage/' . $post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                    @else
                        <img src="https://via.placeholder.com/400x200.png?text=Efarina+TV" alt="Default Image" class="w-full h-48 object-cover">
                    @endif
                </a>
                <div class="p-6">
                    <span class="text-sm font-semibold text-blue-600">{{ $post->category->name }}</span>
                    <h2 class="mt-2 mb-2 font-bold text-2xl text-gray-900 leading-tight">
                        <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>
                    <p class="text-gray-600 text-sm">
                        Oleh {{ $post->author->name }} &bull; {{ $post->published_at->format('d F Y') }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-center col-span-3 text-gray-500">Belum ada berita yang dipublikasikan.</p>
        @endforelse
    </div>

    <div class="mt-12">
        {{ $posts->links() }}
    </div>
</div>
@endsection