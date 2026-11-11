@extends('website.layouts.app')

@php($seoContent = $page)

@section('content')
    <div class="page-shell">
        <div class="site-container">
            <div class="page-hero mb-12">
                <span class="site-pill">{{ $page?->hero_eyebrow ?: 'Selected work' }}</span>
                <h1 class="site-heading mt-6">{{ $page?->hero_heading ?: 'Selected work and transformation stories.' }}</h1>
                <p class="page-lead mt-5">{{ $page?->hero_description ?: 'Explore the real outcomes, products, and platforms shaped with Rexxsol teams.' }}</p>
            </div>
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach (($projects ?? $items ?? []) as $item)
                    <article class="site-card group overflow-hidden">
                        @if ($item->featured_image)
                            <div class="site-media"><img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="{{ \App\Support\ContentImage::alt($item->featured_image, $item->featured_image_alt, $item->name) }}" class="h-72 w-full object-cover" /></div>
                        @else
                            <div class="flex h-72 items-end border-b border-[#e5eaf2] bg-[#f7f9fc] p-7"><span class="site-eyebrow">Project image unavailable</span></div>
                        @endif
                        <div class="p-7">
                            <p class="site-eyebrow">{{ $item->category ?? 'Case study' }}</p>
                            <h2 class="mt-3 text-2xl font-bold text-[#172033]">{{ $item->name }}</h2>
                            @if ($item->short_description)
                                <p class="site-copy mt-3 text-sm">{{ $item->short_description }}</p>
                            @endif
                            <a href="{{ route('website.contact') }}" class="site-link mt-6">Start a similar project <span aria-hidden="true">&#8594;</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
@endsection
