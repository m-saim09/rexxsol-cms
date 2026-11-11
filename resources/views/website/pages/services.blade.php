@extends('website.layouts.app')

@php($withPreview = fn (string $url): string => ($previewTheme ?? null) ? $url.(str_contains($url, '?') ? '&' : '?').'theme='.urlencode($previewTheme) : $url)

@php($seoContent = $page)

@section('content')
    <div class="page-shell">
        <div class="site-container">
            <div class="page-hero mb-12">
                <span class="site-pill">{{ $page?->hero_eyebrow ?: 'Services · Rexxsol capabilities' }}</span>
                <h1 class="site-heading mt-6">{{ $page?->hero_heading ?: 'Technology services built for operational clarity.' }}</h1>
                <p class="page-lead mt-5">{{ $page?->hero_description ?: 'Practical digital capabilities for teams turning complex operations into better ways of working.' }}</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $item)
                    <article class="site-card group flex flex-col p-6">
                        <div class="corporate-icon mb-7 flex h-12 w-12 items-center justify-center border border-[#dbe2ed] text-lg font-bold">{{ Str::upper(Str::substr($item->name, 0, 1)) }}</div>
                        <h2 class="text-xl font-bold text-[#172033]">{{ $item->name }}</h2>
                        @if ($item->short_description)
                            <p class="site-copy mt-3 text-sm">{{ $item->short_description }}</p>
                        @endif
                        <a href="{{ $withPreview(route('website.service', $item->slug)) }}" class="site-link mt-auto pt-7">Explore service <span aria-hidden="true">&#8594;</span></a>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
@endsection
