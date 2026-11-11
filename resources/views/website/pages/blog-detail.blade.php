@extends('website.layouts.app')

@php($seoContent = $item)

@section('content')
    <div class="page-shell">
        <div class="site-container">
            <div class="breadcrumb mb-8 flex items-center gap-2"><a href="{{ route('website.blog') }}">Blog</a><span>/</span><span>{{ $item->title }}</span></div>
            <article class="content-card mx-auto max-w-4xl p-5 sm:p-8 lg:p-12">
            @if ($item->featured_image)
                <div class="page-image mb-8"><img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="{{ \App\Support\ContentImage::alt($item->featured_image, $item->featured_image_alt, $item->title) }}" class="h-80 sm:h-[28rem]" /></div>
            @endif
            <p class="site-eyebrow">{{ $item->published_at ? $item->published_at->format('M d, Y') : 'Published' }}</p>
            <h1 class="mt-3 text-3xl font-bold text-[#172033] sm:text-5xl">{{ $item->title }}</h1>
            @if ($item->excerpt)
                <p class="page-lead mt-6">{{ $item->excerpt }}</p>
            @endif
            @if ($item->content)
                <div class="prose prose-slate mt-8 max-w-3xl">{!! $item->content !!}</div>
            @endif
            </article>
        </div>
    </div>
@endsection
