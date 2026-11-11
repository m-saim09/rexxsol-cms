@extends('website.layouts.app')

@php($seoContent = $page)

@section('content')
    <div class="page-shell">
        <div class="site-container">
            <div class="page-hero mb-12">
                <span class="site-pill">{{ $page?->hero_eyebrow ?: 'Rexxsol insights' }}</span>
                <h1 class="site-heading mt-6">{{ $page?->hero_heading ?: 'Perspectives and practical advice for digital growth.' }}</h1>
                <p class="page-lead mt-5">{{ $page?->hero_description ?: 'Ideas from the work: product delivery, technology strategy, and operational change.' }}</p>
            </div>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($items as $item)
                    <article class="site-card group overflow-hidden">
                        @if ($item->featured_image)
                            <div class="site-media"><img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="{{ \App\Support\ContentImage::alt($item->featured_image, $item->featured_image_alt, $item->title) }}" class="h-56 w-full object-cover" /></div>
                        @else
                            <div class="flex h-56 items-end border-b border-[#e5eaf2] bg-[#f7f9fc] p-5 text-xs font-semibold uppercase tracking-[0.14em] text-[#94a3b8]">Article image unavailable</div>
                        @endif
                        <div class="p-6">
                            <p class="site-eyebrow">{{ $item->published_at ? $item->published_at->format('M d, Y') : 'Published' }}</p>
                            <h2 class="mt-3 text-xl font-bold text-[#172033]">{{ $item->title }}</h2>
                            @if ($item->excerpt)
                                <p class="site-copy mt-3 text-sm">{{ $item->excerpt }}</p>
                            @endif
                            <a href="{{ route('website.blog.post', $item->slug) }}" class="site-link mt-6">Read article <span aria-hidden="true">&#8594;</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
@endsection
