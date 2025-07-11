@extends('layouts.app')

@section('title', $post->title . ' - Efarina TV')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        @if($post->thumbnail)
            <img src="{{ asset('storage/' . $post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-96 object-cover">
        @endif

        <div class="p-6 md:p-10">
            <h1 class="text-3xl md:text-5xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>
            <div class="text-gray-600 text-md mb-6">
                <span>Oleh: <strong>{{ $post->author->name }}</strong></span> | 
                <span>Kategori: <strong>{{ $post->category->name }}</strong></span> | 
                <span>Diterbitkan: <strong>{{ $post->published_at->format('d F Y') }}</strong></span>
            </div>

            <div class="prose max-w-none text-gray-800 text-lg leading-relaxed">
                {!! $post->content !!}
            </div>

            @if($post->tags->isNotEmpty())
                <div class="mt-8 border-t pt-6">
                    <h3 class="text-xl font-semibold mb-3">Tags:</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($post->tags as $tag)
                            <span class="bg-gray-200 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
<div class="mt-10">
    <a href="{{ route('home') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Daftar Berita</a>
</div>
        </div>
    </div>
</div>

<style>
    .prose h2 { font-size: 1.875rem; margin-top: 2em; margin-bottom: 1em; font-weight: 700;}
    .prose h3 { font-size: 1.5rem; margin-top: 1.8em; margin-bottom: 1em; font-weight: 600;}
    .prose p { margin-bottom: 1.25em; }
    .prose ul, .prose ol { margin-left: 1.5em; margin-bottom: 1.25em; }
</style>
@endsection